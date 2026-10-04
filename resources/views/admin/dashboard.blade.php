<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-4">
                    {{ __('Quick Actions') }}
                </h3>
                <div class="flex flex-wrap gap-3">
                    @can('create', App\Models\Post::class)
                        <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Create Post') }}
                        </a>
                    @endcan

                    @can('create', App\Models\Project::class)
                        <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-semibold hover:bg-gray-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Create Project') }}
                        </a>
                    @endcan

                    @can('create', App\Models\LearningPath::class)
                        <a href="{{ route('admin.learning-paths.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Create Learning Path') }}
                        </a>
                    @endcan

                    @can('viewAny', App\Models\ContactMessage::class)
                        <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ __('View Contact Messages') }}
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Overview Metrics Grid -->
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-4">
                    {{ __('Overview Metrics') }}
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Blog Posts Metric -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Blog Posts') }}</span>
                            <span class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $metrics['total_posts'] }}</div>
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                <span><strong class="text-emerald-600 font-semibold">{{ $metrics['published_posts'] }}</strong> {{ __('Published') }}</span>
                                <span>&bull;</span>
                                <span><strong class="text-amber-600 font-semibold">{{ $metrics['draft_posts'] }}</strong> {{ __('Drafts') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Portfolio Projects Metric -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Projects') }}</span>
                            <span class="p-2 bg-gray-100 text-gray-700 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $metrics['total_projects'] }}</div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ __('Total portfolio projects') }}
                            </div>
                        </div>
                    </div>

                    <!-- Learning Platform Metric -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Learning') }}</span>
                            <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $metrics['learning_paths'] }}</div>
                            <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ __('Learning Paths') }}</span>
                                <span>&bull;</span>
                                <span><strong class="text-gray-700 font-semibold">{{ $metrics['lessons'] }}</strong> {{ __('Lessons') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Users Metric -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Users') }}</span>
                            <span class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-gray-900">{{ $metrics['total_users'] }}</div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ __('Registered accounts') }}
                            </div>
                        </div>
                    </div>

                    <!-- Contact Messages Metric -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 sm:col-span-2 lg:col-span-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">{{ __('Contact Messages') }}</span>
                            <span class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                        </div>
                        <div class="mt-4 flex items-baseline gap-8">
                            <div>
                                <div class="text-3xl font-bold text-gray-900">{{ $metrics['total_messages'] }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ __('Total Received') }}</div>
                            </div>
                            <div class="border-l border-gray-200 pl-8">
                                <div class="text-3xl font-bold {{ $metrics['unread_messages'] > 0 ? 'text-indigo-600' : 'text-gray-900' }}">
                                    {{ $metrics['unread_messages'] }}
                                </div>
                                <div class="mt-1 text-xs text-gray-500">{{ __('Unread Messages') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity: Posts & Projects -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Posts -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Recent Posts') }}</h3>
                        <a href="{{ route('admin.posts.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                            {{ __('View all') }} &rarr;
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentPosts as $post)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 pr-4">
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1">
                                                {{ $post->title }}
                                            </a>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                {{ $post->category?->name ?? __('Uncategorized') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-2 whitespace-nowrap text-right">
                                            @if ($post->status === 'published')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    {{ __('Published') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                    {{ __('Draft') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-6 text-center text-sm text-gray-500">
                                            {{ __('No posts found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Recent Projects') }}</h3>
                        <a href="{{ route('admin.projects.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                            {{ __('View all') }} &rarr;
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($recentProjects as $project)
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 pr-4">
                                            <a href="{{ route('admin.projects.edit', $project) }}" class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1">
                                                {{ $project->title }}
                                            </a>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                {{ $project->category?->name ?? __('Uncategorized') }}
                                            </div>
                                        </td>
                                        <td class="py-3 px-2 whitespace-nowrap text-right">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                                {{ $project->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-6 text-center text-sm text-gray-500">
                                            {{ __('No projects found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Contact Messages -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Recent Contact Messages') }}</h3>
                    <a href="{{ route('admin.contact-messages.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                        {{ __('View all') }} &rarr;
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($recentMessages as $message)
                                <tr class="hover:bg-gray-50 {{ is_null($message->read_at) ? 'bg-indigo-50/20' : '' }}">
                                    <td class="py-3 pr-4 whitespace-nowrap">
                                        @if (is_null($message->read_at))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                {{ __('Unread') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                {{ __('Read') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        <div class="font-medium text-gray-900">{{ $message->name }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $message->email }}</div>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <div class="text-gray-900 line-clamp-1">{{ $message->subject }}</div>
                                    </td>
                                    <td class="py-3 pr-4 whitespace-nowrap text-xs text-gray-500">
                                        <time datetime="{{ $message->created_at->toIso8601String() }}">
                                            {{ $message->created_at->format('M d, Y') }}
                                        </time>
                                    </td>
                                    <td class="py-3 whitespace-nowrap text-right">
                                        <a href="{{ route('admin.contact-messages.show', $message) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                            {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-sm text-gray-500">
                                        {{ __('No contact messages found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
