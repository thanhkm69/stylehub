<?php

use App\Models\BlogCategory;
use App\Models\CommentModerationSetting;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    if (! in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $this->markTestSkipped('PDO SQLite is required to run database-backed comment tests.');
    }

    $this->artisan('migrate:fresh');
});

function publishedPostForComments(): Post
{
    $category = BlogCategory::create([
        'name' => 'Style',
        'slug' => 'style',
    ]);

    return Post::create([
        'blog_category_id' => $category->id,
        'title' => 'Office style',
        'slug' => 'office-style',
        'content' => 'Article body',
        'status' => 'published',
        'published_at' => now(),
    ]);
}

test('user can reply to a visible root comment but cannot create a second nesting level', function () {
    $post = publishedPostForComments();
    $author = User::factory()->create();
    $replier = User::factory()->create();

    $root = PostComment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'content' => 'Root comment',
        'status' => true,
    ]);

    Sanctum::actingAs($replier);

    $response = $this->postJson("/api/blogs/{$post->slug}/comments", [
        'content' => 'A reply',
        'parent_id' => $root->id,
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.parent_id', $root->id);

    $reply = PostComment::query()->where('parent_id', $root->id)->firstOrFail();

    $this->postJson("/api/blogs/{$post->slug}/comments", [
        'content' => 'Nested reply',
        'parent_id' => $reply->id,
    ])->assertUnprocessable();

    $this->getJson("/api/blogs/{$post->slug}/comments")
        ->assertOk()
        ->assertJsonPath('data.0.replies.0.content', 'A reply');
});

test('enabled groq moderation hides comments flagged by the safeguard model', function () {
    $post = publishedPostForComments();
    $user = User::factory()->create();

    CommentModerationSetting::create(['enabled' => true]);
    config([
        'services.groq.api_key' => 'test-key',
        'services.groq.base_url' => 'https://api.groq.com/openai/v1',
        'services.groq.moderation_model' => 'openai/gpt-oss-safeguard-20b',
    ]);

    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => '{"violation":1,"category":"harassment","rationale":"Nội dung xúc phạm người khác."}',
                ],
            ]],
        ]),
    ]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/blogs/{$post->slug}/comments", [
        'content' => 'Unsafe text',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', false)
        ->assertJsonPath('data.moderation_status', 'flagged')
        ->assertJsonPath('data.moderation_categories.0', 'harassment');

    $this->getJson("/api/blogs/{$post->slug}/comments")
        ->assertOk()
        ->assertJsonCount(0, 'data');

    Http::assertSent(fn ($request) => $request['model'] === 'openai/gpt-oss-safeguard-20b'
        && $request['messages'][1]['content'] === 'Unsafe text');
});

test('admin can enable automatic comment moderation', function () {
    $admin = User::factory()->create(['role' => 'Admin']);

    Sanctum::actingAs($admin, ['Admin']);

    $this->getJson('/api/admin/post-comments/moderation/settings')
        ->assertOk()
        ->assertJsonPath('data.enabled', false);

    $this->patchJson('/api/admin/post-comments/moderation/toggle')
        ->assertOk()
        ->assertJsonPath('data.enabled', true);
});

test('admin can run groq moderation again for an existing pending comment', function () {
    $post = publishedPostForComments();
    $author = User::factory()->create();
    $admin = User::factory()->create(['role' => 'Admin']);

    CommentModerationSetting::create(['enabled' => true]);
    config([
        'services.groq.api_key' => 'test-key',
        'services.groq.base_url' => 'https://api.groq.com/openai/v1',
        'services.groq.moderation_model' => 'openai/gpt-oss-safeguard-20b',
    ]);

    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => '{"violation":0,"category":null,"rationale":"Nội dung phù hợp."}',
                ],
            ]],
        ]),
    ]);

    $comment = PostComment::create([
        'post_id' => $post->id,
        'user_id' => $author->id,
        'content' => 'Trang phục này rất đẹp.',
        'status' => false,
        'moderation_status' => 'pending',
    ]);

    Sanctum::actingAs($admin, ['Admin']);

    $this->patchJson("/api/admin/post-comments/{$comment->id}/moderate")
        ->assertOk()
        ->assertJsonPath('data.status', true)
        ->assertJsonPath('data.moderation_status', 'approved');
});

test('obfuscated vietnamese profanity is hidden without being approved by ai', function () {
    $post = publishedPostForComments();
    $user = User::factory()->create();

    CommentModerationSetting::create(['enabled' => true]);

    Sanctum::actingAs($user);

    $response = $this->postJson("/api/blogs/{$post->slug}/comments", [
        'content' => 'c.o.n c@c',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', false)
        ->assertJsonPath('data.moderation_status', 'flagged')
        ->assertJsonPath('data.moderation_categories.0', 'profanity');

    $this->getJson("/api/blogs/{$post->slug}/comments")
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

test('unsigned and abbreviated vietnamese profanity is hidden without blocking embedded text', function () {
    $post = publishedPostForComments();
    $user = User::factory()->create();

    CommentModerationSetting::create(['enabled' => true]);
    config([
        'services.groq.api_key' => 'test-key',
        'services.groq.base_url' => 'https://api.groq.com/openai/v1',
        'services.groq.moderation_model' => 'openai/gpt-oss-safeguard-20b',
    ]);

    Http::fake([
        'https://api.groq.com/openai/v1/chat/completions' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => '{"violation":0,"category":null,"rationale":"Nội dung phù hợp."}',
                ],
            ]],
        ]),
    ]);

    Sanctum::actingAs($user);

    foreach (['vai lon', 'lon', 'cc', 'vcl', 'v.c.l'] as $content) {
        $this->postJson("/api/blogs/{$post->slug}/comments", [
            'content' => $content,
        ])->assertCreated()
            ->assertJsonPath('data.status', false)
            ->assertJsonPath('data.moderation_categories.0', 'profanity');
    }

    $this->postJson("/api/blogs/{$post->slug}/comments", [
        'content' => 'Mẫu vải nylon này đẹp.',
    ])->assertCreated()
        ->assertJsonPath('data.status', true)
        ->assertJsonPath('data.moderation_status', 'approved');
});
