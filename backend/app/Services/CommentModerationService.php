<?php

namespace App\Services;

use App\Models\CommentModerationSetting;
use App\Models\PostComment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class CommentModerationService
{
    /**
     * @return array{enabled: bool, configured: bool, model: string, provider: string}
     */
    public function settings(): array
    {
        return [
            'enabled' => (bool) CommentModerationSetting::query()->value('enabled'),
            'configured' => $this->hasApiKey(),
            'model' => (string) config('services.groq.moderation_model'),
            'provider' => 'Groq',
        ];
    }

    public function toggle(): CommentModerationSetting
    {
        $settings = CommentModerationSetting::query()->firstOrCreate([], ['enabled' => false]);
        $settings->update(['enabled' => ! $settings->enabled]);

        return $settings;
    }

    public function moderate(PostComment $comment, bool $isNewComment = false): PostComment
    {
        if (! $this->settings()['enabled']) {
            $comment->update([
                'status' => $isNewComment ? true : $comment->status,
                'moderation_status' => 'not_checked',
                'moderation_reason' => null,
                'moderation_categories' => null,
                'moderated_at' => null,
            ]);

            return $comment->refresh();
        }

        $blockedContent = $this->detectBlockedContent($comment);

        if ($blockedContent !== null) {
            return $this->flag($comment, $blockedContent['reason'], [$blockedContent['category']]);
        }

        if (! $this->hasApiKey()) {
            return $this->pending($comment, 'Chưa cấu hình GROQ_API_KEY để kiểm duyệt tự động.');
        }

        try {
            $response = Http::acceptJson()
                ->withToken(config('services.groq.api_key'))
                ->timeout(15)
                ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => config('services.groq.moderation_model'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $this->moderationPolicy(),
                        ],
                        [
                            'role' => 'user',
                            'content' => $comment->content,
                        ],
                    ],
                ])
                ->throw();

            $classification = $this->parseClassification(
                (string) $response->json('choices.0.message.content', '')
            );

            if (! array_key_exists('violation', $classification)) {
                return $this->pending($comment, 'Groq không trả về kết quả kiểm duyệt hợp lệ, cần duyệt thủ công.');
            }

            if ((int) $classification['violation'] === 1) {
                $category = trim((string) ($classification['category'] ?? 'inappropriate'));
                $reason = trim((string) ($classification['rationale'] ?? 'Nội dung vi phạm tiêu chuẩn cộng đồng.'));

                return $this->flag($comment, $reason, [$category]);
            }

            $comment->update([
                'status' => true,
                'moderation_status' => 'approved',
                'moderation_reason' => null,
                'moderation_categories' => [],
                'moderated_at' => now(),
            ]);

            return $comment->refresh();
        } catch (Throwable) {
            return $this->pending($comment, 'Không thể kết nối Groq, cần duyệt thủ công.');
        }
    }

    private function moderationPolicy(): string
    {
        return <<<'POLICY'
You moderate Vietnamese blog comments for a fashion website.
Return JSON only using this schema:
{"violation":0|1,"category":string|null,"rationale":string}

VIOLATION (1):
- profanity or obscene sexual language intended to insult or disturb others;
- harassment, personal attacks, hate or discrimination;
- sexual content inappropriate for a public fashion blog;
- threats or violent encouragement;
- advertising, scam, repeated promotional text, or spam.

SAFE (0):
- polite opinions, criticism, questions and ordinary fashion discussion;
- mild disagreement without insults;
- genuine references to products or outfits.

Use a short Vietnamese rationale. Do not add markdown or text outside JSON.
POLICY;
    }

    private function hasApiKey(): bool
    {
        $apiKey = trim((string) config('services.groq.api_key'));

        return $apiKey !== '' && $apiKey !== 'your_new_groq_key';
    }

    /**
     * @return array<string, mixed>
     */
    private function parseClassification(string $content): array
    {
        if (preg_match('/\{.*\}/s', $content, $match) !== 1) {
            return [];
        }

        $classification = json_decode($match[0], true);

        return is_array($classification) ? $classification : [];
    }

    /**
     * @return array{reason: string, category: string}|null
     */
    private function detectBlockedContent(PostComment $comment): ?array
    {
        $normalizedContent = Str::lower(Str::ascii($comment->content));
        $normalizedContent = strtr($normalizedContent, [
            '@' => 'a',
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '$' => 's',
        ]);
        $wordContent = trim(preg_replace('/[^a-z0-9]+/', ' ', $normalizedContent) ?? '');
        $compactContent = preg_replace('/[^a-z0-9]+/', '', $normalizedContent) ?? '';

        $compactBlockedTerms = [
            'concac',
            'conkak',
            'ditme',
            'duma',
            'duime',
            'vailon',
            'fuckyou',
        ];

        foreach ($compactBlockedTerms as $term) {
            if (str_contains($compactContent, $term)) {
                return [
                    'reason' => 'Phát hiện ngôn từ thô tục hoặc không phù hợp.',
                    'category' => 'profanity',
                ];
            }
        }

        $standaloneTerms = ['cc', 'vcl', 'lon'];

        foreach ($standaloneTerms as $term) {
            if (preg_match('/(?:^|\s)'.preg_quote($term, '/').'(?:\s|$)/', $wordContent) === 1
                || $compactContent === $term) {
                return [
                    'reason' => 'Phát hiện từ viết tắt hoặc ngôn từ không phù hợp.',
                    'category' => 'profanity',
                ];
            }
        }

        if (preg_match_all('/(?:https?:\/\/|www\.)/iu', $comment->content) >= 2) {
            return [
                'reason' => 'Phát hiện nội dung có nhiều liên kết nghi là spam.',
                'category' => 'spam',
            ];
        }

        if (preg_match('/(.)\1{7,}/u', $comment->content) === 1) {
            return [
                'reason' => 'Phát hiện nội dung lặp lại bất thường nghi là spam.',
                'category' => 'spam',
            ];
        }

        $isDuplicate = PostComment::query()
            ->where('user_id', $comment->user_id)
            ->where('id', '!=', $comment->id)
            ->where('content', $comment->content)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->exists();

        return $isDuplicate ? [
            'reason' => 'Phát hiện bình luận trùng lặp trong thời gian ngắn.',
            'category' => 'spam',
        ] : null;
    }

    /**
     * @param  array<int, string>  $categories
     */
    private function flag(PostComment $comment, string $reason, array $categories): PostComment
    {
        $comment->update([
            'status' => false,
            'moderation_status' => 'flagged',
            'moderation_reason' => $reason,
            'moderation_categories' => $categories,
            'moderated_at' => now(),
        ]);

        return $comment->refresh();
    }

    private function pending(PostComment $comment, string $reason): PostComment
    {
        $comment->update([
            'status' => false,
            'moderation_status' => 'pending',
            'moderation_reason' => $reason,
            'moderation_categories' => null,
            'moderated_at' => now(),
        ]);

        return $comment->refresh();
    }
}
