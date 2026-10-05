<x-public-layout>
    <x-slot:title>
        {{ $lesson->title . ' — ' . $learningPath->title }}
    </x-slot:title>

    <x-slot:metaDescription>
        {{ $lesson->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($lesson->content), 160) }}
    </x-slot:metaDescription>

    <x-slot:canonicalUrl>
        {{ route('learn.lesson', [$learningPath, $lesson]) }}
    </x-slot:canonicalUrl>

    <x-slot:metaType>article</x-slot:metaType>

    @if ($learningPath->thumbnail)
        <x-slot:metaImage>{{ $learningPath->thumbnail }}</x-slot:metaImage>
    @endif

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Breadcrumb & Back Navigation -->
            <nav class="flex items-center text-sm font-medium text-gray-500 space-x-2">
                <a href="{{ route('learn.index') }}" class="hover:text-indigo-600 transition">
                    {{ __('Learn') }}
                </a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <a href="{{ route('learn.show', $learningPath) }}" class="hover:text-indigo-600 transition truncate max-w-xs sm:max-w-md">
                    {{ $learningPath->title }}
                </a>
            </nav>

            <!-- Lesson Header Card -->
            <header class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-10 shadow-sm space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                        {{ __('Lesson') }} {{ $currentLessonIndex }} {{ __('of') }} {{ $totalPublishedLessons }}
                    </span>

                    <a href="{{ route('learn.show', $learningPath) }}"
                       class="text-xs font-semibold text-gray-500 hover:text-indigo-600 transition">
                        &larr; {{ __('Back to Overview') }}
                    </a>
                </div>

                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    {{ $lesson->title }}
                </h1>

                @if ($lesson->excerpt)
                    <p class="text-base sm:text-lg text-gray-600 leading-relaxed font-normal">
                        {{ $lesson->excerpt }}
                    </p>
                @endif
            </header>

            <!-- Lesson Content Area -->
            <main class="bg-white rounded-2xl border border-gray-200 p-6 sm:p-10 shadow-sm">
                <div class="text-gray-800 leading-relaxed whitespace-pre-wrap font-sans text-base sm:text-lg overflow-x-auto">
{{ $lesson->content }}
                </div>
            </main>

            <!-- Sequential Navigation (Previous / Next) -->
            <footer class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200">
                <div>
                    @if ($previousLesson)
                        <a href="{{ route('learn.lesson', [$learningPath, $previousLesson]) }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-3 border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition group">
                            <svg class="mr-2 w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>{{ __('Previous Lesson') }}</span>
                        </a>
                    @endif
                </div>

                <div>
                    @if ($nextLesson)
                        <a href="{{ route('learn.lesson', [$learningPath, $nextLesson]) }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 shadow-sm transition group">
                            <span>{{ __('Next Lesson') }}</span>
                            <svg class="ml-2 w-4 h-4 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @endif
                </div>
            </footer>
        </div>
    </div>
</x-public-layout>
