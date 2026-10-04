<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.contact-messages.index') }}"
                   class="inline-flex items-center text-xs font-semibold text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to Messages') }}
                </a>
                <span class="text-gray-300">|</span>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Message Details') }}
                </h2>
            </div>

            <div>
                @if ($contactMessage->read_at)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                        {{ __('Read on') }} {{ $contactMessage->read_at->format('M d, Y H:i') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                        {{ __('Unread') }}
                    </span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 p-8 space-y-8">
                {{-- Metadata Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-gray-100 text-sm">
                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Sender Name') }}</span>
                        <span class="font-bold text-gray-900 text-base">{{ $contactMessage->name }}</span>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Sender Email') }}</span>
                        <a href="mailto:{{ $contactMessage->email }}" class="font-medium text-indigo-600 hover:underline">
                            {{ $contactMessage->email }}
                        </a>
                    </div>

                    <div class="sm:col-span-2">
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Subject') }}</span>
                        <h1 class="text-xl font-extrabold text-gray-900 mt-0.5">{{ $contactMessage->subject }}</h1>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Received At') }}</span>
                        <time datetime="{{ $contactMessage->created_at->toIso8601String() }}" class="text-gray-700">
                            {{ $contactMessage->created_at->format('l, F j, Y — H:i:s') }}
                        </time>
                    </div>

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Status') }}</span>
                        <span class="text-gray-700">
                            @if ($contactMessage->read_at)
                                {{ __('Read') }} ({{ $contactMessage->read_at->diffForHumans() }})
                            @else
                                {{ __('Unread') }}
                            @endif
                        </span>
                    </div>
                </div>

                {{-- Message Body --}}
                <div class="space-y-2">
                    <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">{{ __('Message Content') }}</span>
                    <div class="p-6 bg-gray-50 rounded-xl border border-gray-100 text-gray-800 leading-relaxed text-sm whitespace-pre-line font-normal">
                        {{ $contactMessage->message }}
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="pt-6 border-t border-gray-100 flex items-center justify-between">
                    <a href="{{ route('admin.contact-messages.index') }}"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl text-xs font-semibold text-gray-700 bg-white hover:bg-gray-50 transition">
                        {{ __('Return to List') }}
                    </a>

                    <a href="mailto:{{ $contactMessage->email }}?subject={{ rawurlencode('Re: ' . $contactMessage->subject) }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold hover:bg-indigo-700 transition">
                        <span>{{ __('Reply via Email') }}</span>
                        <svg class="ml-1.5 w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
