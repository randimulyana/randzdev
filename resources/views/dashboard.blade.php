<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                </div>
            </div>

            @can('viewAny', App\Models\ContactMessage::class)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">{{ __('Contact Messages') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('View and review messages submitted through the public contact form.') }}</p>
                    </div>
                    <a href="{{ route('admin.contact-messages.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                        {{ __('View Messages') }}
                    </a>
                </div>
            @endcan
        </div>
    </div>
</x-app-layout>
