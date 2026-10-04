<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin overview dashboard with system metrics and recent activities.
     */
    public function index(Request $request): View
    {
        abort_unless($request->user()?->hasRole('admin'), 403);

        $metrics = [
            'total_posts' => Post::count(),
            'published_posts' => Post::published()->count(),
            'draft_posts' => Post::where('status', 'draft')->count(),
            'total_projects' => Project::count(),
            'learning_paths' => LearningPath::count(),
            'lessons' => Lesson::count(),
            'total_users' => User::count(),
            'total_messages' => ContactMessage::count(),
            'unread_messages' => ContactMessage::whereNull('read_at')->count(),
        ];

        $recentPosts = Post::query()
            ->with(['category', 'author'])
            ->latest()
            ->take(5)
            ->get();

        $recentProjects = Project::query()
            ->with('category')
            ->latest()
            ->take(5)
            ->get();

        $recentMessages = ContactMessage::query()
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'metrics',
            'recentPosts',
            'recentProjects',
            'recentMessages'
        ));
    }
}
