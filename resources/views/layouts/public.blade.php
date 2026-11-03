<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' . config('app.name', 'Randz.dev') : config('app.name', 'Randz.dev') . ' — Portfolio' }}</title>

        @if (! empty($metaRobots))
            <meta name="robots" content="{{ $metaRobots }}">
        @endif

        @if (! empty($metaDescription))
            <meta name="description" content="{{ $metaDescription }}">
        @endif

        @if (! empty($canonicalUrl))
            <link rel="canonical" href="{{ $canonicalUrl }}">
        @endif

        @php
            $pageTitle = isset($title) ? trim(strip_tags((string) $title)) : config('app.name', 'Randz.dev') . ' — Portfolio';
            $pageDescription = ! empty($metaDescription)
                ? trim(strip_tags((string) $metaDescription))
                : config('portfolio.profile.headline', 'I build web applications and document what I learn about programming.');
            $pageUrl = ! empty($canonicalUrl) ? (string) $canonicalUrl : url()->current();
            $pageType = ! empty($metaType) ? (string) $metaType : 'website';
            $pageImage = ! empty($metaImage) ? (string) $metaImage : null;
            if ($pageImage && ! \Illuminate\Support\Str::startsWith($pageImage, ['http://', 'https://'])) {
                $pageImage = url($pageImage);
            }
        @endphp

        <!-- Open Graph / Facebook -->
        <meta property="og:site_name" content="{{ config('app.name', 'Randz.dev') }}">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $pageDescription }}">
        <meta property="og:url" content="{{ $pageUrl }}">
        <meta property="og:type" content="{{ $pageType }}">
        @if (! empty($pageImage))
            <meta property="og:image" content="{{ $pageImage }}">
        @endif

        <!-- Twitter / X Card -->
        <meta name="twitter:card" content="{{ ! empty($twitterCard) ? $twitterCard : (! empty($pageImage) ? 'summary_large_image' : 'summary') }}">
        <meta name="twitter:title" content="{{ $pageTitle }}">
        <meta name="twitter:description" content="{{ $pageDescription }}">
        @if (! empty($pageImage))
            <meta name="twitter:image" content="{{ $pageImage }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Theme Initialization (prevents flash of unthemed content) -->
        <script data-theme-init>
            (function() {
                try {
                    const theme = localStorage.getItem('theme') || 'system';
                    const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (_) {}
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 flex flex-col min-h-screen transition-colors duration-200">
        <!-- Navigation -->
        <header class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800 sticky top-0 z-30 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center space-x-8">
                        <a href="{{ url('/') }}" class="flex items-center space-x-2 font-bold text-lg text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                            <x-application-logo class="h-8 w-auto fill-current text-indigo-600 dark:text-indigo-400" />
                            <span>{{ config('app.name', 'Randz.dev') }}</span>
                        </a>

                        <nav class="hidden md:flex space-x-6 text-sm font-medium">
                            <a href="{{ route('home') }}"
                               class="transition {{ request()->routeIs('home') ? 'text-indigo-600 font-semibold dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ __('Home') }}
                            </a>
                            <a href="{{ route('about') }}"
                               class="transition {{ request()->routeIs('about') ? 'text-indigo-600 font-semibold dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ __('About') }}
                            </a>
                            <a href="{{ route('projects.index') }}"
                               class="transition {{ request()->routeIs('projects.*') ? 'text-indigo-600 font-semibold dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ __('Projects') }}
                            </a>
                            <a href="{{ route('blog.index') }}"
                               class="transition {{ request()->routeIs('blog.*') ? 'text-indigo-600 font-semibold dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ __('Blog') }}
                            </a>
                            <a href="{{ route('learn.index') }}"
                               class="transition {{ request()->routeIs('learn.*') ? 'text-indigo-600 font-semibold dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ __('Learn') }}
                            </a>
                            <a href="{{ route('contact') }}"
                               class="transition {{ request()->routeIs('contact*') ? 'text-indigo-600 font-semibold dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ __('Contact') }}
                            </a>
                            <a href="{{ route('search') }}"
                               class="transition {{ request()->routeIs('search') ? 'text-indigo-600 font-semibold dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }}">
                                {{ __('Search') }}
                            </a>
                        </nav>
                    </div>

                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <a href="{{ route('search') }}"
                           class="transition {{ request()->routeIs('search') ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white' }} p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                           title="{{ __('Search') }}"
                           aria-label="{{ __('Search') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </a>

                        {{-- Theme Toggle Component --}}
                        <x-theme-toggle />

                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition px-2 py-1">
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition px-2 py-1">
                                {{ __('Log in') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-3.5 py-1.5 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 text-xs font-semibold rounded-lg hover:bg-gray-800 dark:hover:bg-gray-200 transition">
                                    {{ __('Register') }}
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow">
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-950 border-t border-gray-200 dark:border-gray-800 mt-16 py-8 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 dark:text-gray-400 space-y-2">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Randz.dev') }}. {{ __('Portfolio & Programming Learning Platform.') }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ __('Build. Learn. Document. Share.') }}</p>
            </div>
        </footer>
    </body>
</html>
