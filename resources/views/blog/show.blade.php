<x-public-layout>
    <x-slot:title>
        {{ $post->meta_title ?: $post->title }}
    </x-slot:title>

    <x-slot:metaDescription>
        {{ $post->meta_description ?: ($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 160)) }}
    </x-slot:metaDescription>

    <x-slot:canonicalUrl>
        {{ $post->canonical_url ?: route('blog.show', $post) }}
    </x-slot:canonicalUrl>

    <x-slot:metaType>article</x-slot:metaType>

    @if ($post->featured_image)
        <x-slot:metaImage>{{ $post->featured_image }}</x-slot:metaImage>
    @endif

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            {{-- Back Navigation --}}
            <div>
                <a href="{{ route('blog.index') }}"
                   class="inline-flex items-center text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition group">
                    <svg class="mr-2 w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span>{{ __('Back to Blog') }}</span>
                </a>
            </div>

            {{-- Article Header Card --}}
            <header class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                {{-- Featured Image Banner --}}
                @if ($post->featured_image)
                    <div class="aspect-video w-full bg-gray-100 dark:bg-gray-900 overflow-hidden">
                        <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-6 sm:p-10 space-y-6">
                    {{-- Category, Type & Meta --}}
                    <div class="flex flex-wrap items-center gap-3">
                        @if ($post->category)
                            <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                               class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition">
                                {{ $post->category->name }}
                            </a>
                        @endif

                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-300">
                            {{ ucfirst(str_replace('_', ' ', $post->type)) }}
                        </span>

                        @if ($post->reading_time)
                            <span class="inline-flex items-center text-xs font-medium text-gray-500 dark:text-gray-400">
                                <svg class="w-3.5 h-3.5 mr-1 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $post->reading_time }} {{ __('min read') }}
                            </span>
                        @endif
                    </div>

                    {{-- Title & Lead Excerpt --}}
                    <div class="space-y-4">
                        <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">
                            {{ $post->title }}
                        </h1>

                        @if ($post->excerpt)
                            <p class="text-lg text-gray-600 dark:text-gray-300 leading-relaxed font-normal">
                                {{ $post->excerpt }}
                            </p>
                        @endif
                    </div>

                    {{-- Author Byline & Date --}}
                    <div class="flex items-center space-x-3 pt-2 border-t border-gray-100 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($post->author?->name ?? 'R', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white leading-none">
                                {{ $post->author?->name ?? 'Randz' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                <time datetime="{{ $post->published_at?->toIso8601String() }}">
                                    {{ $post->published_at?->format('F d, Y') }}
                                </time>
                            </p>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Article Content Card --}}
            <main class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 sm:p-10 shadow-sm space-y-8">
                <div class="prose max-w-none text-gray-800 dark:text-gray-200 leading-relaxed text-base sm:text-lg markdown-content">
                    {!! \App\Support\Markdown::render($post->content) !!}
                </div>

                {{-- Tags Footer --}}
                @if ($post->tags->isNotEmpty())
                    <div class="pt-6 border-t border-gray-100 dark:border-gray-700 space-y-2">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">{{ __('Tags') }}</span>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                                   class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 hover:text-indigo-700 dark:hover:text-indigo-300 transition">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </main>

            {{-- Back Link Footer --}}
            <div class="pt-4 text-center">
                <a href="{{ route('blog.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition shadow-sm">
                    &larr; {{ __('Back to all articles') }}
                </a>
            </div>
        </div>
    </div>
</x-public-layout>
