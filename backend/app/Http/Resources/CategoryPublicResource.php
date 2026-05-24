<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryPublicResource extends JsonResource
{
    /**
     * Transform the resource into an array (public category with children).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'slug'     => $this->slug,
            'image'    => $this->image,
            'display'  => $this->display,
            'children' => CategoryPublicResource::collection($this->whenLoaded('activeChildrenRecursive')),
        ];
    }
}
