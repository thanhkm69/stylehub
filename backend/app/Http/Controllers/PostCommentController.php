<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostCommentRequest;
use App\Http\Requests\UpdatePostCommentRequest;
use App\Http\Resources\PostCommentResource;
use App\Models\Post;
use App\Models\PostComment;
use App\Services\CommentModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function __construct(private readonly CommentModerationService $moderationService) {}

    public function index(Request $request, Post $post): JsonResponse
    {
        $comments = $post->comments()
            ->with([
                'user',
                'replies' => fn ($query) => $query->where('status', true)->with('user')->oldest(),
            ])
            ->whereNull('parent_id')
            ->where('status', true)
            ->latest()
            ->paginate(min(max($request->integer('limit', 10), 1), 50));

        return response()->json([
            'success' => true,
            'data' => PostCommentResource::collection($comments),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'total' => $post->comments()->where('status', true)->count(),
            ],
        ]);
    }

    public function store(StorePostCommentRequest $request, Post $post): JsonResponse
    {
        $parent = null;

        if ($request->filled('parent_id')) {
            $parent = PostComment::query()
                ->whereKey($request->integer('parent_id'))
                ->where('post_id', $post->id)
                ->where('status', true)
                ->first();

            if (! $parent || $parent->parent_id !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ có thể trả lời bình luận gốc trong bài viết này.',
                ], 422);
            }
        }

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'parent_id' => $parent?->id,
            'content' => $request->validated('content'),
            'status' => false,
        ]);

        $comment = $this->moderationService->moderate($comment, true);

        return response()->json([
            'success' => true,
            'message' => $comment->status
                ? 'Bình luận của bạn đã được đăng.'
                : 'Bình luận đã được gửi và đang chờ quản trị viên duyệt.',
            'data' => new PostCommentResource($comment->load('user')),
        ], 201);
    }

    public function update(UpdatePostCommentRequest $request, PostComment $postComment): JsonResponse
    {
        if ($postComment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền sửa bình luận này.',
            ], 403);
        }

        $postComment->update([
            'content' => $request->validated('content'),
        ]);

        $postComment = $this->moderationService->moderate($postComment);

        return response()->json([
            'success' => true,
            'message' => $postComment->status
                ? 'Cập nhật bình luận thành công.'
                : 'Bình luận đã được cập nhật và đang chờ quản trị viên duyệt.',
            'data' => new PostCommentResource($postComment->load('user')),
        ]);
    }

    public function destroy(Request $request, PostComment $postComment): JsonResponse
    {
        if ($postComment->user_id !== $request->user()->id && strcasecmp((string) $request->user()->role, 'admin') !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xóa bình luận này.',
            ], 403);
        }

        $postComment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa bình luận thành công.',
        ]);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $query = PostComment::query()->with(['user', 'post', 'parent.user']);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $query->where(function ($query) use ($search) {
                $query->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('post', fn ($postQuery) => $postQuery->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', filter_var($request->status, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('moderation_status')) {
            $query->where('moderation_status', $request->string('moderation_status')->toString());
        }

        $sortMap = [
            'created_at_asc' => ['created_at', 'asc'],
            'created_at_desc' => ['created_at', 'desc'],
        ];
        $sort = $sortMap[$request->string('sort')->toString()] ?? $sortMap['created_at_desc'];
        $query->orderBy($sort[0], $sort[1]);

        $comments = $query->paginate(min(max($request->integer('limit', 15), 1), 100));

        return response()->json([
            'success' => true,
            'data' => PostCommentResource::collection($comments),
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    public function adminToggleStatus(PostComment $postComment): JsonResponse
    {
        $willBeVisible = ! $postComment->status;

        $postComment->update([
            'status' => $willBeVisible,
            'moderation_status' => $willBeVisible ? 'approved' : 'hidden',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái bình luận thành công.',
            'data' => new PostCommentResource($postComment->load(['user', 'post', 'parent.user'])),
        ]);
    }

    public function adminModerate(PostComment $postComment): JsonResponse
    {
        if (! $this->moderationService->settings()['enabled']) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng bật kiểm duyệt AI trước khi kiểm tra bình luận.',
            ], 422);
        }

        $postComment = $this->moderationService->moderate($postComment);

        return response()->json([
            'success' => true,
            'message' => $postComment->status
                ? 'AI đã kiểm tra và duyệt bình luận.'
                : 'AI đã kiểm tra; bình luận vẫn cần quản trị viên duyệt.',
            'data' => new PostCommentResource($postComment->load(['user', 'post', 'parent.user'])),
        ]);
    }

    public function adminModerationSettings(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->moderationService->settings(),
        ]);
    }

    public function adminToggleModeration(): JsonResponse
    {
        $this->moderationService->toggle();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chế độ kiểm duyệt AI thành công.',
            'data' => $this->moderationService->settings(),
        ]);
    }
}
