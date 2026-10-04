<x-public-layout>
    <x-slot:title>
        {{ __('Blog & Articles') }}
    </x-slot:title>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            {{-- Header Section --}}
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Blog & Articles') }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600">
                    {{ __('Insights, guides, tutorials, and engineering notes on modern software development.') }}
                </p>
            </div>

            {{-- Category Filter Tabs --}}
            @if ($categories->isNotEmpty())
                <div class="flex items-center justify-center flex-wrap gap-2 pt-2">
                    <a href="{{ route('blog.index', array_filter(['tag' => $currentTag])) }}"
                       class="inline-flex items-center px-4 py-2 rounded-full text-xs sm:text-sm font-medium transition {{ ! $currentCategory ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                        {{ __('All Categories') }}
                    </a>

                    @foreach ($categories as $cat)
                        <a href="{{ route('blog.index', array_filter(['category' => $cat->slug, 'tag' => $currentTag])) }}"
                           class="inline-flex items-center px-4 py-2 rounded-full text-xs sm:text-sm font-medium transition {{ $currentCategory === $cat->slug ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Tag Filter Pills --}}
            @if ($tags->isNotEmpty())
                <div class="flex items-center justify-center flex-wrap gap-2 pt-1">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mr-1">{{ __('Tags:') }}</span>
                    <a href="{{ route('blog.index', array_filter(['category' => $currentCategory])) }}"
                       class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition {{ ! $currentTag ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ __('All') }}
                    </a>
                    @foreach ($tags as $t)
                        <a href="{{ route('blog.index', array_filter(['category' => $currentCategory, 'tag' => $t->slug])) }}"
                           class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition {{ $currentTag === $t->slug ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            #{{ $t->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Active Filter Indicators --}}
            @if ($currentCategory || $currentTag)
                <div class="flex items-center justify-center gap-2 pt-1">
                    <span class="text-xs text-gray-500">{{ __('Active filter:') }}</span>
                    @if ($currentCategory)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ __('Category:') }} {{ $currentCategory }}
                        </span>
                    @endif
                    @if ($currentTag)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                            {{ __('Tag:') }} #{{ $currentTag }}
                        </span>
                    @endif
                    <a href="{{ route('blog.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-800 ml-2">
                        {{ __('Clear all') }}
                    </a>
                </div>
            @endif

            {{-- Post Grid --}}
            @if ($posts->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($posts as $post)
                        <article class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                            {{-- Thumbnail / Banner --}}
                            <a href="{{ route('blog.show', $post) }}" class="block relative aspect-video bg-gray-100 overflow-hidden group">
                                @if ($post->featured_image)
                                    <img src="{{ $post->featured_image }}"
                                         alt="{{ $post->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                @else
                                    <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 text-indigo-400 group-hover:from-indigo-100 transition duration-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                        </svg>
                                        <span class="text-xs text-gray-500 mt-2 font-medium">{{ __('Article') }}</span>
                                    </div>
                                @endif

                                {{-- Type Badge --}}
                                <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-600 text-white shadow">
                                    {{ ucfirst(str_replace('_', ' ', $post->type)) }}
                                </span>
                            </a>

                            {{-- Card Body --}}
                            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between text-xs text-gray-500">
                                        @if ($post->category)
                                            <a href="{{ route('blog.index', ['category' => $post->category->slug]) }}"
                                               class="font-semibold text-indigo-600 uppercase tracking-wider hover:underline">
                                                {{ $post->category->name }}
                                            </a>
                                        @else
                                            <span></span>
                                        @endif

                                        @if ($post->reading_time)
                                            <span class="inline-flex items-center text-gray-500">
                                                <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                {{ $post->reading_time }} {{ __('min read') }}
                                            </span>
                                        @endif
                                    </div>

                                    <h2 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition">
                                        <a href="{{ route('blog.show', $post) }}">
                                            {{ $post->title }}
                                        </a>
                                    </h2>

                                    <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                        {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 130) }}
                                    </p>
                                </div>

                                {{-- Meta & Tags --}}
                                <div class="space-y-4 pt-4 border-t border-gray-100">
                                    @if ($post->tags->isNotEmpty())
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($post->tags as $tag)
                                                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}"
                                                   class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">
                                                    #{{ $tag->name }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between pt-1 text-xs text-gray-500">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-medium text-gray-800">{{ $post->author?->name ?? 'Randz' }}</span>
                                            <span>&bull;</span>
                                            <time datetime="{{ $post->published_at?->toIso8601String() }}">
                                                {{ $post->published_at?->format('M d, Y') }}
                                            </time>
                                        </div>

                                        <a href="{{ route('blog.show', $post) }}"
                                           class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                            <span>{{ __('Read') }}</span>
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

                {{-- Pagination --}}
                @if ($posts->hasPages())
                    <div class="pt-6">
                        {{ $posts->links() }}
                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="text-center py-16 bg-white rounded-xl border border-gray-200 p-8 max-w-xl mx-auto space-y-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('No articles found') }}</h3>
                    <p class="text-sm text-gray-500">
                        @if ($currentCategory || $currentTag)
                            {{ __('There are no published articles matching your filter criteria.') }}
                        @else
                            {{ __('No published articles are available at the moment.') }}
                        @endif
                    </p>
                    @if ($currentCategory || $currentTag)
                        <div class="pt-2">
                            <a href="{{ route('blog.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                                {{ __('View all articles') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
