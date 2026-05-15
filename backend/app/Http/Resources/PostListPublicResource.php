<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostListPublicResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ];
            }),
            'published_at' => $this->published_at,
        ];
    }
}
