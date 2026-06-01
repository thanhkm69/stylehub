<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'status' => $this->status,
            'moderation_status' => $this->moderation_status,
            'moderation_reason' => $this->moderation_reason,
            'moderation_categories' => $this->moderation_categories,
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ]),
            'post' => $this->whenLoaded('post', fn () => [
                'id' => $this->post->id,
                'title' => $this->post->title,
                'slug' => $this->post->slug,
            ]),
            'parent' => $this->whenLoaded('parent', fn () => [
                'id' => $this->parent->id,
                'content' => $this->parent->content,
                'user' => [
                    'id' => $this->parent->user?->id,
                    'name' => $this->parent->user?->name,
                ],
            ]),
            'replies' => PostCommentResource::collection($this->whenLoaded('replies')),
            'moderated_at' => $this->moderated_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
