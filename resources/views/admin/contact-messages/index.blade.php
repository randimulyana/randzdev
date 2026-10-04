<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Contact Messages') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-medium text-xs">
                                <tr>
                                    <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Sender') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Subject') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Date') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($contactMessages as $message)
                                    <tr class="hover:bg-gray-50 {{ is_null($message->read_at) ? 'font-semibold bg-indigo-50/30' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if (is_null($message->read_at))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                                    {{ __('Unread') }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    {{ __('Read') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-gray-900 font-medium">{{ $message->name }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $message->email }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-gray-900 line-clamp-1 max-w-md">{{ $message->subject }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                            <time datetime="{{ $message->created_at->toIso8601String() }}">
                                                {{ $message->created_at->format('M d, Y H:i') }}
                                            </time>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('admin.contact-messages.show', $message) }}"
                                               class="inline-flex items-center text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition">
                                                {{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                                            {{ __('No contact messages found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($contactMessages->hasPages())
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            {{ $contactMessages->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
