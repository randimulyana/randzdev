<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicBlogController extends Controller
{
    /**
     * Display a listing of published posts.
     */
    public function index(Request $request): View
    {
        $currentCategory = $request->query('category');
        $currentTag = $request->query('tag');

        $categories = Category::query()
            ->whereHas('posts', function ($query) {
                $query->published();
            })
            ->orderBy('name')
            ->get();

        $tags = Tag::query()
            ->whereHas('posts', function ($query) {
                $query->published();
            })
            ->orderBy('name')
            ->get();

        $posts = Post::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->when($currentCategory, function ($query, $categorySlug) {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->when($currentTag, function ($query, $tagSlug) {
                $query->whereHas('tags', function ($q) use ($tagSlug) {
                    $q->where('slug', $tagSlug);
                });
            })
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', compact('posts', 'categories', 'tags', 'currentCategory', 'currentTag'));
    }

    /**
     * Display the specified published post.
     */
    public function show(string $slug): View
    {
        $post = Post::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('blog.show', compact('post'));
    }
}
