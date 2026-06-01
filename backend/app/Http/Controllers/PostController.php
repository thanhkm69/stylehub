<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('category');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['published', 'draft'], true)) {
            $query->where('status', $request->string('status')->toString());
        }

        $sortMap = [
            'created_at_asc' => ['created_at', 'asc'],
            'created_at_desc' => ['created_at', 'desc'],
        ];

        $sort = $sortMap[$request->string('sort')->toString()] ?? $sortMap['created_at_desc'];
        $query->orderBy($sort[0], $sort[1]);

        $limit = min(max($request->integer('limit', 10), 1), 100);
        $posts = $query->paginate($limit);

        return PostResource::collection($posts);
    }

    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create($data);

        return new PostResource($post->load('category'));
    }

    public function show(Post $post)
    {
        return new PostResource($post->load('category'));
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);

        return new PostResource($post->load('category'));
    }

    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        return response()->noContent();
    }
}
