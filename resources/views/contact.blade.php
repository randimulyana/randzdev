<x-public-layout>
    <x-slot:title>{{ __('Contact') }}</x-slot:title>
    <x-slot:metaDescription>{{ __('Get in touch with ' . config('portfolio.profile.name', 'Randi') . ' for software engineering inquiries, collaborations, or questions.') }}</x-slot:metaDescription>
    <x-slot:canonicalUrl>{{ route('contact') }}</x-slot:canonicalUrl>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            {{-- Header --}}
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="inline-flex items-center px-3.5 py-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                    {{ __('Get In Touch') }}
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 tracking-tight">
                    {{ __('Contact Me') }}
                </h1>
                <p class="text-base sm:text-lg text-gray-600 leading-relaxed font-normal">
                    {{ __('Have a project in mind, an engineering question, or want to collaborate? Send a message and I will get back to you as soon as possible.') }}
                </p>
            </div>

            {{-- Flash Success Feedback --}}
            @if (session('success'))
                <div class="max-w-3xl mx-auto rounded-2xl bg-green-50 border border-green-200 p-4 sm:p-6 text-green-800 flex items-start space-x-3 shadow-sm">
                    <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="font-semibold text-green-900">{{ __('Message Sent!') }}</h3>
                        <p class="text-sm mt-0.5 text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Contact Information Card --}}
                <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 shadow-sm space-y-8 flex flex-col justify-between">
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ __('Contact Information') }}
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ __('Reach out directly via email or social profiles.') }}
                            </p>
                        </div>

                        <div class="space-y-4 text-sm text-gray-600">
                            @if (! empty(config('portfolio.social.email')))
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <div>
                                        <span class="block text-xs font-semibold uppercase text-gray-400">{{ __('Email') }}</span>
                                        <a href="mailto:{{ config('portfolio.social.email') }}" class="font-medium text-gray-800 hover:text-indigo-600 transition">
                                            {{ config('portfolio.social.email') }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if (! empty(config('portfolio.profile.location')))
                                <div class="flex items-start space-x-3">
                                    <svg class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <div>
                                        <span class="block text-xs font-semibold uppercase text-gray-400">{{ __('Location') }}</span>
                                        <span class="font-medium text-gray-800">{{ config('portfolio.profile.location') }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Social Links --}}
                    @if (config('portfolio.social'))
                        <div class="pt-6 border-t border-gray-100 space-y-3">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                                {{ __('Connect Online') }}
                            </h3>
                            <div class="flex flex-wrap gap-3 text-sm">
                                @if (! empty(config('portfolio.social.github')))
                                    <a href="{{ config('portfolio.social.github') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-700 hover:text-indigo-600 transition">
                                        {{ __('GitHub') }}
                                    </a>
                                @endif
                                @if (! empty(config('portfolio.social.linkedin')))
                                    <a href="{{ config('portfolio.social.linkedin') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-700 hover:text-indigo-600 transition">
                                        {{ __('LinkedIn') }}
                                    </a>
                                @endif
                                @if (! empty(config('portfolio.social.website')))
                                    <a href="{{ config('portfolio.social.website') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-gray-700 hover:text-indigo-600 transition">
                                        {{ __('Website') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Contact Form --}}
                <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-200 p-6 sm:p-10 shadow-sm">
                    <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700">
                                    {{ __('Name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') }}"
                                       required
                                       maxlength="255"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('name') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror"
                                       placeholder="{{ __('Your Name') }}">
                                @error('name')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-semibold text-gray-700">
                                    {{ __('Email') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="email"
                                       name="email"
                                       id="email"
                                       value="{{ old('email') }}"
                                       required
                                       maxlength="255"
                                       class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('email') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror"
                                       placeholder="{{ __('you@example.com') }}">
                                @error('email')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label for="subject" class="block text-sm font-semibold text-gray-700">
                                {{ __('Subject') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="subject"
                                   id="subject"
                                   value="{{ old('subject') }}"
                                   required
                                   maxlength="255"
                                   class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('subject') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror"
                                   placeholder="{{ __('What is this regarding?') }}">
                            @error('subject')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div>
                            <label for="message" class="block text-sm font-semibold text-gray-700">
                                {{ __('Message') }} <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message"
                                      id="message"
                                      rows="6"
                                      required
                                      maxlength="5000"
                                      class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm @error('message') border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500 @enderror"
                                      placeholder="{{ __('Your message...') }}">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div>
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold shadow hover:bg-indigo-700 transition">
                                <span>{{ __('Send Message') }}</span>
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
