<x-public-layout>
    <x-slot:title>{!! config('portfolio.profile.name', 'Randi') !!} — {!! config('portfolio.profile.role', 'Web Developer & Builder') !!}</x-slot:title>
    <x-slot:metaDescription>{{ config('portfolio.profile.headline', 'I build web applications and document what I learn about programming.') }}</x-slot:metaDescription>
    <x-slot:canonicalUrl>{{ route('home') }}</x-slot:canonicalUrl>

    <div class="py-12 space-y-20">
        {{-- 1. Hero Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-12 sm:py-20 lg:py-24 text-center max-w-4xl mx-auto space-y-8">
                <div class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ config('portfolio.profile.role', 'Web Developer & Builder') }}
                </div>

                <h1 class="text-4xl sm:text-6xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    {{ __('Hi, I\'m') }} <span class="text-indigo-600">{{ config('portfolio.profile.name', 'Randi') }}</span>.
                </h1>

                <p class="text-lg sm:text-2xl text-gray-600 font-normal leading-relaxed max-w-2xl mx-auto">
                    {{ config('portfolio.profile.headline', 'I build web applications and document what I learn about programming.') }}
                </p>

                {{-- Hero CTAs --}}
                <div class="flex flex-wrap items-center justify-center gap-4 pt-4">
                    <a href="{{ route('projects.index') }}"
                       class="inline-flex items-center px-6 py-3.5 bg-gray-900 text-white rounded-xl text-sm font-semibold shadow hover:bg-gray-800 transition">
                        <span>{{ __('View Projects') }}</span>
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>

                    <a href="{{ route('learn.index') }}"
                       class="inline-flex items-center px-6 py-3.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold shadow hover:bg-indigo-700 transition">
                        <span>{{ __('Start Learning') }}</span>
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </a>
                </div>

                {{-- Social Links --}}
                @if (config('portfolio.social'))
                    <div class="flex items-center justify-center space-x-6 pt-4 text-sm text-gray-500">
                        @if (! empty(config('portfolio.social.github')))
                            <a href="{{ config('portfolio.social.github') }}" target="_blank" rel="noopener noreferrer" class="hover:text-gray-900 transition font-medium">
                                {{ __('GitHub') }}
                            </a>
                        @endif
                        @if (! empty(config('portfolio.social.linkedin')))
                            <a href="{{ config('portfolio.social.linkedin') }}" target="_blank" rel="noopener noreferrer" class="hover:text-indigo-600 transition font-medium">
                                {{ __('LinkedIn') }}
                            </a>
                        @endif
                        @if (! empty(config('portfolio.social.email')))
                            <a href="mailto:{{ config('portfolio.social.email') }}" class="hover:text-indigo-600 transition font-medium">
                                {{ __('Email') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        {{-- 2. Introduction Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-gray-200 p-8 sm:p-12 shadow-sm max-w-4xl mx-auto text-center space-y-4">
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
                    {{ __('About My Work & Philosophy') }}
                </h2>
                <p class="text-base sm:text-lg text-gray-600 leading-relaxed font-normal">
                    {{ config('portfolio.profile.bio', 'Passionate software engineer focused on building production-grade web systems, clean backend architecture, and sharing practical programming knowledge.') }}
                </p>
                <div class="pt-2">
                    <a href="{{ route('about') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                        <span>{{ __('Learn more about my background') }}</span>
                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        {{-- 3. Featured Projects Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex items-center justify-between flex-wrap gap-4 border-b border-gray-200 pb-5">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ __('Featured Projects') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Production software engineering and practical solutions built with modern stacks.') }}
                    </p>
                </div>
                <a href="{{ route('projects.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                    <span>{{ __('View All Projects') }}</span>
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            @if ($featuredProjects->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($featuredProjects as $project)
                        <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                            <a href="{{ route('projects.show', $project) }}" class="block relative aspect-video bg-gray-100 overflow-hidden group">
                                @if ($project->thumbnail)
                                    <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 text-indigo-400 group-hover:from-indigo-100 transition duration-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white shadow">
                                    {{ __('Featured') }}
                                </span>
                            </a>

                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    @if ($project->category)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                            {{ $project->category->name }}
                                        </span>
                                    @endif

                                    <h3 class="text-xl font-bold text-gray-900 leading-snug">
                                        <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600 transition">
                                            {{ $project->title }}
                                        </a>
                                    </h3>

                                    <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                        {{ $project->short_description ?: $project->description }}
                                    </p>
                                </div>

                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                                        <span>{{ __('Case Study') }}</span>
                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-2xl border border-gray-200 p-8 space-y-2">
                    <p class="text-base font-semibold text-gray-800">{{ __('No featured projects available yet.') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Check out all completed engineering projects in the portfolio showcase.') }}</p>
                    <div class="pt-3">
                        <a href="{{ route('projects.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                            {{ __('Explore All Projects') }}
                        </a>
                    </div>
                </div>
            @endif
        </section>

        {{-- 4. Learning Section & Learning Paths --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex items-center justify-between flex-wrap gap-4 border-b border-gray-200 pb-5">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ __('Learning Platform') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Structured, free learning tracks to master software development step by step.') }}
                    </p>
                </div>
                <a href="{{ route('learn.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                    <span>{{ __('View All Tracks') }}</span>
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            @if ($learningPaths->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($learningPaths as $path)
                        <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                            <a href="{{ route('learn.show', $path) }}" class="block relative aspect-video bg-gray-100 overflow-hidden group">
                                @if ($path->thumbnail)
                                    <img src="{{ $path->thumbnail }}" alt="{{ $path->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 text-indigo-500 group-hover:from-indigo-100 transition duration-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                @endif
                            </a>

                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                            {{ ucfirst($path->difficulty) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $path->lessons_count }} {{ \Illuminate\Support\Str::plural(__('lesson'), $path->lessons_count) }}
                                        </span>
                                    </div>

                                    <h3 class="text-xl font-bold text-gray-900 leading-snug">
                                        <a href="{{ route('learn.show', $path) }}" class="hover:text-indigo-600 transition">
                                            {{ $path->title }}
                                        </a>
                                    </h3>

                                    @if ($path->description)
                                        <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                            {{ $path->description }}
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <a href="{{ route('learn.show', $path) }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                                        <span>{{ __('Start Track') }}</span>
                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-2xl border border-gray-200 p-8 space-y-2">
                    <p class="text-base font-semibold text-gray-800">{{ __('No learning paths published yet.') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Curriculum tracks are currently in development.') }}</p>
                </div>
            @endif
        </section>

        {{-- 5. Latest Articles Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="flex items-center justify-between flex-wrap gap-4 border-b border-gray-200 pb-5">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ __('Latest Articles') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Deep dives into backend architecture, programming techniques, and engineering tutorials.') }}
                    </p>
                </div>
                <a href="{{ route('blog.index') }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                    <span>{{ __('View All Articles') }}</span>
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            @if ($latestPosts->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($latestPosts as $post)
                        <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                            <a href="{{ route('blog.show', $post) }}" class="block relative aspect-video bg-gray-100 overflow-hidden group">
                                @if ($post->featured_image)
                                    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 text-indigo-400 group-hover:from-indigo-100 transition duration-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                @endif
                            </a>

                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <div class="flex items-center gap-2">
                                        @if ($post->category)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                                {{ $post->category->name }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                                            {{ ucfirst(str_replace('_', ' ', $post->type)) }}
                                        </span>
                                    </div>

                                    <h3 class="text-xl font-bold text-gray-900 leading-snug">
                                        <a href="{{ route('blog.show', $post) }}" class="hover:text-indigo-600 transition">
                                            {{ $post->title }}
                                        </a>
                                    </h3>

                                    @if ($post->excerpt)
                                        <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                            {{ $post->excerpt }}
                                        </p>
                                    @endif
                                </div>

                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                                    <time datetime="{{ $post->published_at?->toIso8601String() }}">
                                        {{ $post->published_at?->format('M d, Y') }}
                                    </time>
                                    <a href="{{ route('blog.show', $post) }}" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                                        <span>{{ __('Read') }}</span>
                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-2xl border border-gray-200 p-8 space-y-2">
                    <p class="text-base font-semibold text-gray-800">{{ __('No articles published yet.') }}</p>
                    <p class="text-sm text-gray-500">{{ __('Technical articles and guides will be published soon.') }}</p>
                </div>
            @endif
        </section>

        {{-- 6. Skills Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="border-b border-gray-200 pb-5">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Technical Skills & Stacks') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Technologies, frameworks, and engineering tools applied across production software.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($skills as $categoryName => $skillList)
                    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-2">
                            {{ $categoryName }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($skillList as $skill)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 7. About / Journey Preview Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-8 sm:p-12 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="space-y-4 max-w-2xl">
                    <span class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">
                        {{ __('Developer Journey') }}
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                        {{ __('From Fundamentals to Production Systems') }}
                    </h2>
                    <p class="text-base text-gray-600 leading-relaxed">
                        {{ __('A clear timeline documenting progression through core programming, Laravel architecture, real-world deployment, and engineering case studies.') }}
                    </p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('about') }}"
                       class="inline-flex items-center px-6 py-3.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold shadow hover:bg-indigo-700 transition">
                        <span>{{ __('View Developer Journey') }}</span>
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>

        {{-- 8. Concluding CTA Section --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-gray-900 rounded-3xl p-8 sm:p-16 text-white max-w-4xl mx-auto space-y-6 shadow-xl">
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    {{ __('Ready to explore projects or level up your skills?') }}
                </h2>
                <p class="text-base sm:text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed">
                    {{ __('Explore detailed architecture case studies in projects or follow step-by-step curriculum tracks in the learning platform.') }}
                </p>
                <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                    <a href="{{ route('projects.index') }}"
                       class="inline-flex items-center px-6 py-3.5 bg-white text-gray-900 rounded-xl text-sm font-semibold shadow hover:bg-gray-100 transition">
                        {{ __('Explore Projects') }}
                    </a>
                    <a href="{{ route('learn.index') }}"
                       class="inline-flex items-center px-6 py-3.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold shadow hover:bg-indigo-700 transition">
                        {{ __('Browse Learning Tracks') }}
                    </a>
                </div>
            </div>
        </section>
    </div>
</x-public-layout>
