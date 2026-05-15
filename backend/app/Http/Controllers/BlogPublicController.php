<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\BlogCategory;
use App\Http\Resources\PostListPublicResource;
use App\Http\Resources\PostDetailPublicResource;
use App\Http\Resources\BlogCategoryPublicResource;
use Illuminate\Http\Request;

class BlogPublicController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with('category')->where('status', 'published');

        if ($request->has('category')) {
            $categorySlug = $request->get('category');
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $posts = $query->latest('published_at')->paginate(9);
        return PostListPublicResource::collection($posts);
    }

    public function show($slug)
    {
        $post = Post::with('category')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count if needed in the future
        
        $relatedPosts = Post::with('category')
            ->where('status', 'published')
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->inRandomOrder()
            ->take(3)
            ->get();

        // Fallback if not enough related posts in the same category
        if ($relatedPosts->count() < 3) {
            $fallbackPosts = Post::with('category')
                ->where('status', 'published')
                ->where('id', '!=', $post->id)
                ->whereNotIn('id', $relatedPosts->pluck('id')->toArray())
                ->inRandomOrder()
                ->take(3 - $relatedPosts->count())
                ->get();
            
            $relatedPosts = $relatedPosts->concat($fallbackPosts);
        }

        return (new PostDetailPublicResource($post))->additional([
            'related_posts' => PostListPublicResource::collection($relatedPosts)
        ]);
    }

    public function categories()
    {
        // Only return categories that have published posts
        $categories = BlogCategory::whereHas('posts', function($query) {
            $query->where('status', 'published');
        })->get();
        return BlogCategoryPublicResource::collection($categories);
    }
}
