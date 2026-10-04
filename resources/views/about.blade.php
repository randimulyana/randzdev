<x-public-layout>
    <x-slot:title>{{ __('About') }}</x-slot:title>
    <x-slot:metaDescription>{{ __('Learn more about ' . ($profile['name'] ?? 'Randi') . ', software engineering background, developer journey, and technical skillset.') }}</x-slot:metaDescription>
    <x-slot:canonicalUrl>{{ route('about') }}</x-slot:canonicalUrl>

    <div class="py-12 space-y-16">
        {{-- Profile Header Card --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-3xl border border-gray-200 p-8 sm:p-12 shadow-sm space-y-8">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-8">
                    {{-- Avatar / Initials --}}
                    <div class="flex-shrink-0">
                        @if (! empty($profile['avatar']))
                            <img src="{{ $profile['avatar'] }}" alt="{{ $profile['name'] ?? 'Randi' }}" class="w-24 h-24 sm:w-28 sm:h-28 rounded-full object-cover border-4 border-indigo-50 shadow-sm">
                        @else
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white font-extrabold text-3xl sm:text-4xl flex items-center justify-center shadow-md">
                                {{ strtoupper(substr($profile['name'] ?? 'R', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    {{-- Profile Info --}}
                    <div class="flex-1 space-y-4 text-center sm:text-left">
                        <div>
                            <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 mb-2">
                                {{ $profile['role'] ?? 'Web Developer & Builder' }}
                            </div>
                            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                                {{ $profile['name'] ?? 'Randi' }}
                            </h1>
                        </div>

                        <p class="text-base sm:text-lg text-gray-600 leading-relaxed font-normal">
                            {{ $profile['bio'] ?? '' }}
                        </p>

                        {{-- Metadata Pills: Location, Education, Current Focus --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-sm text-gray-600 text-left">
                            @if (! empty($profile['location']))
                                <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5 flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <span class="block text-xs font-semibold uppercase text-gray-400">{{ __('Location') }}</span>
                                        <span class="font-medium text-gray-800">{{ $profile['location'] }}</span>
                                    </div>
                                </div>
                            @endif

                            @if (! empty($profile['education']))
                                <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5 flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    </svg>
                                    <div>
                                        <span class="block text-xs font-semibold uppercase text-gray-400">{{ __('Education') }}</span>
                                        <span class="font-medium text-gray-800">{{ $profile['education'] }}</span>
                                    </div>
                                </div>
                            @endif

                            @if (! empty($profile['current_focus']))
                                <div class="bg-gray-50 border border-gray-100 rounded-xl p-3.5 flex items-start space-x-3 sm:col-span-2">
                                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    <div>
                                        <span class="block text-xs font-semibold uppercase text-gray-400">{{ __('Current Focus') }}</span>
                                        <span class="font-medium text-gray-800">{{ $profile['current_focus'] }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Social Profiles --}}
                        @if (! empty($social))
                            <div class="flex items-center justify-center sm:justify-start space-x-4 pt-3 border-t border-gray-100 text-sm">
                                @if (! empty($social['github']))
                                    <a href="{{ $social['github'] }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-700 hover:text-indigo-600 transition">
                                        {{ __('GitHub') }}
                                    </a>
                                @endif
                                @if (! empty($social['linkedin']))
                                    <a href="{{ $social['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-700 hover:text-indigo-600 transition">
                                        {{ __('LinkedIn') }}
                                    </a>
                                @endif
                                @if (! empty($social['email']))
                                    <a href="mailto:{{ $social['email'] }}" class="font-semibold text-gray-700 hover:text-indigo-600 transition">
                                        {{ __('Email') }}
                                    </a>
                                @endif
                                @if (! empty($social['website']))
                                    <a href="{{ $social['website'] }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-700 hover:text-indigo-600 transition">
                                        {{ __('Website') }}
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- Developer Journey Section --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="border-b border-gray-200 pb-4">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Developer Journey') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('A chronological timeline documenting my progression, key milestones, and ongoing engineering focus.') }}
                </p>
            </div>

            <div class="relative border-l-2 border-indigo-200 ml-4 sm:ml-6 space-y-8 py-2">
                @foreach ($journey as $step)
                    <div class="relative pl-6 sm:pl-8 group">
                        {{-- Timeline Marker Dot --}}
                        <div class="absolute -left-2.5 top-1.5 w-5 h-5 rounded-full border-4 border-white bg-indigo-600 shadow group-hover:scale-125 transition"></div>

                        <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-2">
                            <span class="inline-block text-xs font-semibold uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full">
                                {{ $step['period'] ?? '' }}
                            </span>
                            <h3 class="text-lg font-bold text-gray-900">
                                {{ $step['title'] ?? '' }}
                            </h3>
                            <p class="text-sm sm:text-base text-gray-600 leading-relaxed font-normal">
                                {{ $step['description'] ?? '' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Categorized Technical Skills --}}
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="border-b border-gray-200 pb-4">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Technical Competencies') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ __('Core languages, frameworks, and tools used to build maintainable software systems.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach ($skills as $categoryTitle => $skillItems)
                    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                        <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-2">
                            {{ $categoryTitle }}
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($skillItems as $skillItem)
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $skillItem }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
</x-public-layout>
