<x-public-layout>
    <x-slot:title>
        {{ __('Portfolio Projects') }}
    </x-slot:title>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header Section -->
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Featured & Recent Projects') }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600">
                    {{ __('Explore real-world software applications, tools, and engineering case studies built across modern stacks.') }}
                </p>
            </div>

            <!-- Category Filter Tabs -->
            @if ($categories->isNotEmpty())
                <div class="flex items-center justify-center flex-wrap gap-2 pt-2">
                    <a href="{{ route('projects.index') }}"
                       class="inline-flex items-center px-4 py-2 rounded-full text-xs sm:text-sm font-medium transition {{ ! $currentCategory ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                        {{ __('All Projects') }}
                    </a>

                    @foreach ($categories as $cat)
                        <a href="{{ route('projects.index', ['category' => $cat->slug]) }}"
                           class="inline-flex items-center px-4 py-2 rounded-full text-xs sm:text-sm font-medium transition {{ $currentCategory === $cat->slug ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <!-- Project Grid -->
            @if ($projects->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($projects as $project)
                        <article class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                            <!-- Thumbnail / Banner -->
                            <a href="{{ route('projects.show', $project) }}" class="block relative aspect-video bg-gray-100 overflow-hidden group">
                                @if ($project->thumbnail)
                                    <img src="{{ $project->thumbnail }}"
                                         alt="{{ $project->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 text-indigo-400 group-hover:from-indigo-100 transition duration-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-xs text-gray-500 mt-2 font-medium">{{ __('Project Showcase') }}</span>
                                    </div>
                                @endif

                                <!-- Featured Pill -->
                                @if ($project->featured)
                                    <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500 text-white shadow">
                                        {{ __('Featured') }}
                                    </span>
                                @endif
                            </a>

                            <!-- Card Body -->
                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs">
                                        @if ($project->category)
                                            <span class="font-semibold text-indigo-600 uppercase tracking-wider">
                                                {{ $project->category->name }}
                                            </span>
                                        @else
                                            <span></span>
                                        @endif

                                        @php
                                            $statusClasses = match ($project->status) {
                                                'completed' => 'bg-green-100 text-green-800',
                                                'in_progress' => 'bg-yellow-100 text-yellow-800',
                                                'archived' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-blue-100 text-blue-800',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClasses }}">
                                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                        </span>
                                    </div>

                                    <h2 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition">
                                        <a href="{{ route('projects.show', $project) }}">
                                            {{ $project->title }}
                                        </a>
                                    </h2>

                                    <p class="text-sm text-gray-600 line-clamp-3">
                                        {{ $project->short_description ?? Str::limit($project->description, 120) }}
                                    </p>
                                </div>

                                <!-- Technologies & Footer Link -->
                                <div class="space-y-4 pt-4 border-t border-gray-100">
                                    @if ($project->technologies->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($project->technologies as $tech)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                    {{ $tech->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pt-1">
                                        <a href="{{ route('projects.show', $project) }}"
                                           class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                            <span>{{ __('View Details') }}</span>
                                            <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($projects->hasPages())
                    <div class="pt-6">
                        {{ $projects->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-16 bg-white rounded-xl border border-gray-200 p-8 max-w-xl mx-auto space-y-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('No projects found') }}</h3>
                    <p class="text-sm text-gray-500">
                        @if ($currentCategory)
                            {{ __('There are no published projects in this category yet.') }}
                        @else
                            {{ __('No published projects are available at the moment.') }}
                        @endif
                    </p>
                    @if ($currentCategory)
                        <div class="pt-2">
                            <a href="{{ route('projects.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                {{ __('View all projects') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
