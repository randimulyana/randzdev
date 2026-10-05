<x-public-layout>
    <x-slot:title>
        {{ $project->title }}
    </x-slot:title>

    <x-slot:metaDescription>
        {{ $project->short_description ?: \Illuminate\Support\Str::limit(strip_tags($project->description ?? ''), 160) }}
    </x-slot:metaDescription>

    <x-slot:canonicalUrl>
        {{ route('projects.show', $project) }}
    </x-slot:canonicalUrl>

    <x-slot:metaType>website</x-slot:metaType>

    @if ($project->thumbnail)
        <x-slot:metaImage>{{ $project->thumbnail }}</x-slot:metaImage>
    @endif

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Back Navigation -->
            <div>
                <a href="{{ route('projects.index') }}"
                   class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-indigo-600 transition group">
                    <svg class="mr-2 w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>{{ __('Back to Projects') }}</span>
                </a>
            </div>

            <!-- Project Header Card -->
            <header class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                <!-- Thumbnail / Media Banner -->
                @if ($project->thumbnail)
                    <div class="aspect-video w-full bg-gray-100 overflow-hidden">
                        <img src="{{ $project->thumbnail }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-6 sm:p-10 space-y-6">
                    <!-- Category & Status Meta -->
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($project->category)
                            <a href="{{ route('projects.index', ['category' => $project->category->slug]) }}"
                               class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition">
                                {{ $project->category->name }}
                            </a>
                        @endif

                        @php
                            $statusClasses = match ($project->status) {
                                'completed' => 'bg-green-100 text-green-800',
                                'in_progress' => 'bg-yellow-100 text-yellow-800',
                                'archived' => 'bg-gray-100 text-gray-800',
                                default => 'bg-blue-100 text-blue-800',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>

                        @if ($project->featured)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                {{ __('Featured Project') }}
                            </span>
                        @endif
                    </div>

                    <!-- Title & Short Description -->
                    <div class="space-y-3">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                            {{ $project->title }}
                        </h1>

                        @if ($project->short_description)
                            <p class="text-lg text-gray-600 leading-relaxed">
                                {{ $project->short_description }}
                            </p>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    @if ($project->live_url || $project->repository_url)
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            @if ($project->live_url)
                                <a href="{{ $project->live_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition">
                                    <span>{{ __('Live Demo') }}</span>
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif

                            @if ($project->repository_url)
                                <a href="{{ $project->repository_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-5 py-2.5 bg-gray-900 border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition">
                                    <span>{{ __('Source Code') }}</span>
                                    <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </header>

            <!-- Metadata Grid (Role, Timeline, Technologies) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @if ($project->role)
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Role') }}</span>
                        <p class="text-sm font-bold text-gray-900">{{ $project->role }}</p>
                    </div>
                @endif

                @if ($project->started_at || $project->completed_at)
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-1">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Timeline') }}</span>
                        <p class="text-sm font-bold text-gray-900">
                            {{ $project->started_at?->format('M Y') ?? '—' }} &ndash;
                            {{ $project->completed_at?->format('M Y') ?? ($project->status === 'completed' ? __('Completed') : __('Ongoing')) }}
                        </p>
                    </div>
                @endif

                @if ($project->technologies->isNotEmpty())
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-2 md:col-span-{{ ($project->role && ($project->started_at || $project->completed_at)) ? '1' : '2' }}">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider block">{{ __('Technologies') }}</span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($project->technologies as $tech)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $tech->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Project Description / Case Study Body -->
            @if ($project->description)
                <div class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-10 shadow-sm space-y-4">
                    <h2 class="text-xl font-bold text-gray-900 border-b border-gray-100 pb-3">
                        {{ __('Project Overview & Details') }}
                    </h2>
                    <div class="prose max-w-none text-gray-700 leading-relaxed whitespace-pre-line text-sm sm:text-base">
                        {{ $project->description }}
                    </div>
                </div>
            @endif

            <!-- Back Link Footer -->
            <div class="pt-4 text-center">
                <a href="{{ route('projects.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition shadow-sm">
                    &larr; {{ __('Back to all projects') }}
                </a>
            </div>
        </div>
    </div>
</x-public-layout>
