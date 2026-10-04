<x-public-layout>
    <x-slot:title>
        {{ $learningPath->title }}
    </x-slot:title>

    <x-slot:metaDescription>
        {{ \Illuminate\Support\Str::limit(strip_tags($learningPath->description ?? ''), 160) }}
    </x-slot:metaDescription>

    <x-slot:canonicalUrl>
        {{ route('learn.show', $learningPath) }}
    </x-slot:canonicalUrl>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Back Navigation -->
            <div>
                <a href="{{ route('learn.index') }}"
                   class="inline-flex items-center text-sm font-semibold text-gray-600 hover:text-indigo-600 transition group">
                    <svg class="mr-2 w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>{{ __('Back to Learning Paths') }}</span>
                </a>
            </div>

            <!-- Learning Path Header Card -->
            <header class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                @if ($learningPath->thumbnail)
                    <div class="aspect-video w-full bg-gray-100 overflow-hidden">
                        <img src="{{ $learningPath->thumbnail }}" alt="{{ $learningPath->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-6 sm:p-10 space-y-6">
                    <!-- Badges & Metadata -->
                    <div class="flex flex-wrap items-center gap-2">
                        @php
                            $difficultyClasses = match ($learningPath->difficulty) {
                                'beginner' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'intermediate' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'advanced' => 'bg-purple-50 text-purple-700 border-purple-200',
                                default => 'bg-gray-50 text-gray-700 border-gray-200',
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $difficultyClasses }}">
                            {{ ucfirst($learningPath->difficulty) }}
                        </span>

                        @if ($learningPath->estimated_duration)
                            <span class="inline-flex items-center text-xs font-medium text-gray-500">
                                <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $learningPath->estimated_duration }}
                            </span>
                        @endif

                        <span class="inline-flex items-center text-xs font-medium text-gray-500">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ $learningPath->lessons->count() }} {{ \Illuminate\Support\Str::plural(__('lesson'), $learningPath->lessons->count()) }}
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <div class="space-y-4">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                            {{ $learningPath->title }}
                        </h1>

                        @if ($learningPath->description)
                            <p class="text-base sm:text-lg text-gray-600 leading-relaxed">
                                {{ $learningPath->description }}
                            </p>
                        @endif
                    </div>

                    <!-- Action CTA -->
                    <div class="pt-2">
                        @if ($firstLesson)
                            <a href="{{ route('learn.lesson', [$learningPath, $firstLesson]) }}"
                               class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold shadow hover:bg-indigo-700 transition">
                                <span>{{ __('Start Learning') }}</span>
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </a>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-500 text-sm font-medium">
                                {{ __('Curriculum in preparation — check back soon') }}
                            </span>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Curriculum Section -->
            <section class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
                        {{ __('Curriculum') }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ __('Lessons are organized sequentially. Follow along in order for the best learning experience.') }}
                    </p>
                </div>

                @if ($learningPath->lessons->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($learningPath->lessons as $lessonItem)
                            <article class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm hover:border-indigo-300 transition flex items-start gap-4">
                                <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold flex items-center justify-center text-sm">
                                    {{ $loop->iteration }}
                                </span>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 hover:text-indigo-600 transition">
                                        <a href="{{ route('learn.lesson', [$learningPath, $lessonItem]) }}">
                                            {{ $lessonItem->title }}
                                        </a>
                                    </h3>

                                    @if ($lessonItem->excerpt)
                                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">
                                            {{ $lessonItem->excerpt }}
                                        </p>
                                    @endif
                                </div>

                                <div class="flex-shrink-0 self-center">
                                    <a href="{{ route('learn.lesson', [$learningPath, $lessonItem]) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition">
                                        <span>{{ __('Start') }}</span>
                                        <svg class="ml-1 w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <!-- Empty Curriculum State -->
                    <div class="text-center py-12 bg-white rounded-xl border border-gray-200 p-8 space-y-3">
                        <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('No lessons published yet') }}</h3>
                        <p class="text-sm text-gray-500">
                            {{ __('There are no published lessons in this learning path yet. Please check back later.') }}
                        </p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-public-layout>
