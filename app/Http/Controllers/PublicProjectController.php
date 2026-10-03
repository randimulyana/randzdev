<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicProjectController extends Controller
{
    /**
     * Display a listing of published projects.
     */
    public function index(Request $request): View
    {
        $currentCategory = $request->query('category');

        $categories = Category::query()
            ->whereHas('projects', function ($query) {
                $query->published();
            })
            ->orderBy('name')
            ->get();

        $projects = Project::query()
            ->published()
            ->with(['category', 'technologies'])
            ->when($currentCategory, function ($query, $categorySlug) {
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            })
            ->orderByDesc('featured')
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('projects.index', compact('projects', 'categories', 'currentCategory'));
    }

    /**
     * Display the specified published project.
     */
    public function show(string $slug): View
    {
        $project = Project::query()
            ->published()
            ->with(['category', 'technologies'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('projects.show', compact('project'));
    }
}
