@props(['align' => 'right'])

@php
    $alignmentClasses = $align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right';
@endphp

<div class="relative inline-block text-left" data-theme-toggle>
    {{-- Trigger Button --}}
    <button type="button"
            data-theme-menu-trigger
            aria-label="{{ __('Select theme: Light, Dark, or System') }}"
            aria-haspopup="true"
            aria-expanded="false"
            title="{{ __('Toggle theme') }}"
            class="p-2 rounded-xl text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 border border-transparent hover:border-gray-200 dark:hover:border-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-indigo-500 dark:focus-visible:ring-offset-gray-900 transition duration-150 cursor-pointer">
        {{-- Sun icon (shown when light theme is active) --}}
        <svg data-theme-icon="light" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>

        {{-- Moon icon (shown when dark theme is active) --}}
        <svg data-theme-icon="dark" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>

        {{-- System/Monitor icon (shown when system theme is active) --}}
        <svg data-theme-icon="system" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
    </button>

    {{-- Dropdown Menu (Vanilla JS Controlled) --}}
    <div data-theme-menu
         role="menu"
         aria-orientation="vertical"
         class="hidden absolute {{ $alignmentClasses }} mt-2 w-36 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg py-1.5 z-50 focus:outline-none text-xs sm:text-sm">
        {{-- Option: Light --}}
        <button type="button"
                data-set-theme="light"
                role="menuitem"
                class="w-full flex items-center justify-between px-3 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 focus:outline-none focus-visible:bg-gray-100 dark:focus-visible:bg-gray-700 transition cursor-pointer">
            <span class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>{{ __('Light') }}</span>
            </span>
            <svg data-theme-check class="w-4 h-4 text-indigo-600 dark:text-indigo-400 opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </button>

        {{-- Option: Dark --}}
        <button type="button"
                data-set-theme="dark"
                role="menuitem"
                class="w-full flex items-center justify-between px-3 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 focus:outline-none focus-visible:bg-gray-100 dark:focus-visible:bg-gray-700 transition cursor-pointer">
            <span class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
                <span>{{ __('Dark') }}</span>
            </span>
            <svg data-theme-check class="w-4 h-4 text-indigo-600 dark:text-indigo-400 opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </button>

        {{-- Option: System --}}
        <button type="button"
                data-set-theme="system"
                role="menuitem"
                class="w-full flex items-center justify-between px-3 py-2 text-left text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60 focus:outline-none focus-visible:bg-gray-100 dark:focus-visible:bg-gray-700 transition cursor-pointer">
            <span class="flex items-center space-x-2">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>{{ __('System') }}</span>
            </span>
            <svg data-theme-check class="w-4 h-4 text-indigo-600 dark:text-indigo-400 opacity-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
        </button>
    </div>
</div>
