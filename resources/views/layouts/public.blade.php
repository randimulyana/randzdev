<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' — ' . config('app.name', 'Randz.dev') : config('app.name', 'Randz.dev') . ' — Portfolio' }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900 flex flex-col min-h-screen">
        <!-- Navigation -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center space-x-8">
                        <a href="{{ url('/') }}" class="flex items-center space-x-2 font-bold text-lg text-gray-900 hover:text-indigo-600 transition">
                            <x-application-logo class="h-8 w-auto fill-current text-indigo-600" />
                            <span>{{ config('app.name', 'Randz.dev') }}</span>
                        </a>

                        <nav class="hidden md:flex space-x-6 text-sm font-medium">
                            <a href="{{ route('projects.index') }}"
                               class="transition {{ request()->routeIs('projects.*') ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                                {{ __('Projects') }}
                            </a>
                            <a href="{{ route('blog.index') }}"
                               class="transition {{ request()->routeIs('blog.*') ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
                                {{ __('Blog') }}
                            </a>
                        </nav>
                    </div>

                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition">
                                {{ __('Dashboard') }}
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">
                                {{ __('Log in') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-3.5 py-1.5 bg-gray-900 text-white text-xs font-semibold rounded-md hover:bg-gray-800 transition">
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
        <footer class="bg-white border-t border-gray-200 mt-16 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 space-y-2">
                <p>&copy; {{ date('Y') }} {{ config('app.name', 'Randz.dev') }}. {{ __('Portfolio & Programming Learning Platform.') }}</p>
                <p class="text-xs text-gray-400">{{ __('Build. Learn. Document. Share.') }}</p>
            </div>
        </footer>
    </body>
</html>
