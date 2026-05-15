<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category')->latest()->get();
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
                \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
            }
            $data['image'] = $request->file('image')->store('posts', 'public');
        }

        $post->update($data);
        return new PostResource($post->load('category'));
    }

    public function destroy(Post $post)
    {
        if ($post->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($post->image);
        }
        $post->delete();
        return response()->noContent();
    }
}
