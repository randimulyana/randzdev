<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\Post;
use App\Models\Project;
use Illuminate\View\View;

class PublicHomeController extends Controller
{
    /**
     * Display the public homepage.
     */
    public function index(): View
    {
        $featuredProjects = Project::query()
            ->published()
            ->where('featured', true)
            ->with(['category', 'technologies'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $learningPaths = LearningPath::query()
            ->published()
            ->withCount([
                'lessons' => fn ($query) => $query->published(),
            ])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $latestPosts = Post::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $skills = config('portfolio.skills', []);

        return view('home', compact('featuredProjects', 'learningPaths', 'latestPosts', 'skills'));
    }

    /**
     * Display the public about page.
     */
    public function about(): View
    {
        $profile = config('portfolio.profile', []);
        $social = config('portfolio.social', []);
        $journey = config('portfolio.journey', []);
        $skills = config('portfolio.skills', []);

        return view('about', compact('profile', 'social', 'journey', 'skills'));
    }
}
