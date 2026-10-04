<x-public-layout>
    <x-slot:title>
        {{ __('Learning Paths') }}
    </x-slot:title>

    <x-slot:metaDescription>
        {{ __('Explore structured programming learning paths from beginner to advanced.') }}
    </x-slot:metaDescription>

    <x-slot:canonicalUrl>
        {{ route('learn.index') }}
    </x-slot:canonicalUrl>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Header Section -->
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                    {{ __('Learning Platform') }}
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Learning Paths') }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600">
                    {{ __('Structured learning tracks to help you master software engineering step by step.') }}
                </p>
            </div>

            <!-- Learning Path Grid -->
            @if ($learningPaths->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($learningPaths as $path)
                        <article class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                            <!-- Thumbnail / Banner -->
                            <a href="{{ route('learn.show', $path) }}" class="block relative aspect-video bg-gray-100 overflow-hidden group">
                                @if ($path->thumbnail)
                                    <img src="{{ $path->thumbnail }}"
                                         alt="{{ $path->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 text-indigo-500 group-hover:from-indigo-100 transition duration-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span class="text-xs text-gray-500 mt-2 font-medium">{{ __('Learning Path') }}</span>
                                    </div>
                                @endif
                            </a>

                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-3">
                                    <!-- Badges & Metadata -->
                                    <div class="flex flex-wrap items-center gap-2">
                                        @php
                                            $difficultyClasses = match ($path->difficulty) {
                                                'beginner' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'intermediate' => 'bg-blue-50 text-blue-700 border-blue-200',
                                                'advanced' => 'bg-purple-50 text-purple-700 border-purple-200',
                                                default => 'bg-gray-50 text-gray-700 border-gray-200',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $difficultyClasses }}">
                                            {{ ucfirst($path->difficulty) }}
                                        </span>

                                        @if ($path->estimated_duration)
                                            <span class="inline-flex items-center text-xs font-medium text-gray-500">
                                                <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $path->estimated_duration }}
                                            </span>
                                        @endif

                                        <span class="inline-flex items-center text-xs font-medium text-gray-500">
                                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            {{ $path->lessons_count }} {{ \Illuminate\Support\Str::plural(__('lesson'), $path->lessons_count) }}
                                        </span>
                                    </div>

                                    <!-- Title -->
                                    <h2 class="text-xl font-bold text-gray-900 leading-snug group">
                                        <a href="{{ route('learn.show', $path) }}" class="hover:text-indigo-600 transition">
                                            {{ $path->title }}
                                        </a>
                                    </h2>

                                    <!-- Description -->
                                    @if ($path->description)
                                        <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                            {{ $path->description }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Card Footer CTA -->
                                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-medium text-gray-400">{{ __('Free Curriculum') }}</span>
                                    <a href="{{ route('learn.show', $path) }}"
                                       class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-700 transition">
                                        <span>{{ __('Explore Path') }}</span>
                                        <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($learningPaths->hasPages())
                    <div class="pt-6">
                        {{ $learningPaths->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-200 p-8 max-w-xl mx-auto space-y-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('No learning paths found') }}</h3>
                    <p class="text-sm text-gray-500">
                        {{ __('No published learning paths are available at the moment.') }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
