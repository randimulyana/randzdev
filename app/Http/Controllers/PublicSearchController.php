<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSearchController extends Controller
{
    /**
     * Display search results for public posts and projects.
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->input('q'));

        if ($query === '') {
            return view('search.index', [
                'query' => '',
                'posts' => null,
                'projects' => null,
            ]);
        }

        $posts = Post::query()
            ->published()
            ->with(['category', 'tags'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhereHas('category', function (Builder $categoryQuery) use ($query) {
                        $categoryQuery->where('name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('tags', function (Builder $tagQuery) use ($query) {
                        $tagQuery->where('name', 'like', "%{$query}%");
                    });
            })
            ->latest('published_at')
            ->paginate(10, ['*'], 'posts_page')
            ->withQueryString();

        $projects = Project::query()
            ->published()
            ->with(['category', 'technologies'])
            ->where(function (Builder $builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%");
            })
            ->latest('published_at')
            ->paginate(10, ['*'], 'projects_page')
            ->withQueryString();

        return view('search.index', compact('posts', 'projects', 'query'));
    }
}
