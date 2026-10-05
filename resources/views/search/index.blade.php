<x-public-layout>
    <x-slot:title>
        {{ __('Search') }}
    </x-slot:title>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            {{-- Header Section --}}
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Search') }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600">
                    {{ __('Find articles, tutorials, topics, and portfolio projects across Randz.dev.') }}
                </p>

                {{-- Search Form --}}
                <form action="{{ route('search') }}" method="GET" class="max-w-2xl mx-auto pt-4">
                    <div class="relative flex items-center">
                        <input type="search"
                               name="q"
                               value="{{ $query }}"
                               placeholder="{{ __('Search articles and projects...') }}"
                               aria-label="{{ __('Search input') }}"
                               class="w-full pl-11 pr-24 py-3 bg-white border border-gray-300 rounded-xl shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <div class="absolute left-3.5 text-gray-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button type="submit"
                                class="absolute right-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow transition">
                            {{ __('Search') }}
                        </button>
                    </div>
                </form>
            </div>

            {{-- Initial Search State --}}
            @if ($query === '')
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm max-w-2xl mx-auto px-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Search articles and projects') }}</h2>
                    <p class="text-sm text-gray-500 max-w-md mx-auto">
                        {{ __('Enter a search keyword above to find published articles, tutorials, engineering guides, and portfolio projects.') }}
                    </p>
                </div>

            {{-- Empty Results State --}}
            @elseif ($posts->total() === 0 && $projects->total() === 0)
                <div class="text-center py-16 bg-white rounded-2xl border border-gray-100 shadow-sm max-w-2xl mx-auto px-6">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">
                        {{ __('No results found for') }} "<span class="text-indigo-600 font-bold">{{ $query }}</span>"
                    </h2>
                    <p class="text-sm text-gray-500 max-w-md mx-auto">
                        {{ __('We could not find any matching articles or projects. Please try different keywords or check for spelling errors.') }}
                    </p>
                </div>

            {{-- Search Results --}}
            @else
                <div class="space-y-12">
                    {{-- Articles Section --}}
                    @if ($posts->total() > 0)
                        <section class="space-y-6">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">{{ __('Articles') }}</h2>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ trans_choice(':count article found|:count articles found', $posts->total(), ['count' => $posts->total()]) }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($posts as $post)
                                    <article class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                                        <div class="p-6 space-y-3">
                                            <div class="flex items-center justify-between text-xs text-gray-500">
                                                @if ($post->category)
                                                    <span class="font-semibold text-indigo-600 uppercase tracking-wider">
                                                        {{ $post->category->name }}
                                                    </span>
                                                @else
                                                    <span></span>
                                                @endif

                                                @if ($post->published_at)
                                                    <time datetime="{{ $post->published_at->toIso8601String() }}">
                                                        {{ $post->published_at->format('M d, Y') }}
                                                    </time>
                                                @endif
                                            </div>

                                            <h3 class="text-lg font-bold text-gray-900 hover:text-indigo-600 transition">
                                                <a href="{{ route('blog.show', $post) }}">
                                                    {{ $post->title }}
                                                </a>
                                            </h3>

                                            @if ($post->excerpt)
                                                <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                                    {{ $post->excerpt }}
                                                </p>
                                            @elseif ($post->content)
                                                <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                                    {{ Str::limit(strip_tags($post->content), 120) }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="px-6 pb-6 pt-0 space-y-4">
                                            @if ($post->tags->isNotEmpty())
                                                <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-100">
                                                    @foreach ($post->tags as $tag)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                            #{{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="pt-2 flex justify-end">
                                                <a href="{{ route('blog.show', $post) }}"
                                                   class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                                    <span>{{ __('Read Article') }}</span>
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            @if ($posts->hasPages())
                                <div class="pt-4">
                                    {{ $posts->links() }}
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- Projects Section --}}
                    @if ($projects->total() > 0)
                        <section class="space-y-6">
                            <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900">{{ __('Projects') }}</h2>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ trans_choice(':count project found|:count projects found', $projects->total(), ['count' => $projects->total()]) }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($projects as $project)
                                    <article class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                                        <div class="p-6 space-y-3">
                                            <div class="flex items-center justify-between text-xs text-gray-500">
                                                @if ($project->category)
                                                    <span class="font-semibold text-indigo-600 uppercase tracking-wider">
                                                        {{ $project->category->name }}
                                                    </span>
                                                @else
                                                    <span></span>
                                                @endif

                                                @if ($project->status)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <h3 class="text-lg font-bold text-gray-900 hover:text-indigo-600 transition">
                                                <a href="{{ route('projects.show', $project) }}">
                                                    {{ $project->title }}
                                                </a>
                                            </h3>

                                            @if ($project->short_description)
                                                <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                                    {{ $project->short_description }}
                                                </p>
                                            @elseif ($project->description)
                                                <p class="text-sm text-gray-600 line-clamp-3 leading-relaxed">
                                                    {{ Str::limit(strip_tags($project->description), 120) }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="px-6 pb-6 pt-0 space-y-4">
                                            @if ($project->technologies->isNotEmpty())
                                                <div class="flex flex-wrap gap-1.5 pt-2 border-t border-gray-100">
                                                    @foreach ($project->technologies as $tech)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">
                                                            {{ $tech->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="pt-2 flex justify-end">
                                                <a href="{{ route('projects.show', $project) }}"
                                                   class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                                    <span>{{ __('View Project') }}</span>
                                                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            @if ($projects->hasPages())
                                <div class="pt-4">
                                    {{ $projects->links() }}
                                </div>
                            @endif
                        </section>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
