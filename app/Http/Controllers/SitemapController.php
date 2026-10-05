<?php

namespace App\Http\Controllers;

use App\Models\LearningPath;
use App\Models\Lesson;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate and display the dynamic XML sitemap.
     */
    public function index(): Response
    {
        $urls = [];

        // Static public pages
        $staticPages = [
            ['route' => 'home', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['route' => 'about', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'projects.index', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'blog.index', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['route' => 'learn.index', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['route' => 'contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];

        foreach ($staticPages as $page) {
            $urls[] = [
                'loc' => route($page['route']),
                'lastmod' => null,
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        // Published Projects
        $projects = Project::query()
            ->published()
            ->latest('updated_at')
            ->get();

        foreach ($projects as $project) {
            $urls[] = [
                'loc' => route('projects.show', $project),
                'lastmod' => ($project->updated_at ?? $project->published_at)?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Published Posts
        $posts = Post::query()
            ->published()
            ->latest('updated_at')
            ->get();

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => route('blog.show', $post),
                'lastmod' => ($post->updated_at ?? $post->published_at)?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Published Learning Paths
        $learningPaths = LearningPath::query()
            ->published()
            ->latest('updated_at')
            ->get();

        foreach ($learningPaths as $path) {
            $urls[] = [
                'loc' => route('learn.show', $path),
                'lastmod' => ($path->updated_at ?? $path->published_at)?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        // Published Lessons (parent learning path must also be published)
        $lessons = Lesson::query()
            ->published()
            ->whereHas('learningPath', function ($query) {
                $query->published();
            })
            ->with('learningPath')
            ->latest('updated_at')
            ->get();

        foreach ($lessons as $lesson) {
            $urls[] = [
                'loc' => route('learn.lesson', [$lesson->learningPath, $lesson]),
                'lastmod' => ($lesson->updated_at ?? $lesson->published_at)?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.7',
            ];
        }

        $content = '<?xml version="1.0" encoding="UTF-8"?>'."\n".view('sitemap', compact('urls'))->render();

        return response($content, 200, [
            'Content-Type' => 'text/xml; charset=UTF-8',
        ]);
    }

    /**
     * Generate dynamic robots.txt response using configured application URL.
     */
    public function robots(): Response
    {
        $sitemapUrl = rtrim((string) config('app.url'), '/').'/sitemap.xml';

        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /dashboard',
            'Allow: /',
            '',
            'Sitemap: '.$sitemapUrl,
        ])."\n";

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
