<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Permission Matrix') }}
                </h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ __('Read-only overview of assigned capabilities across roles.') }}
                </p>
            </div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                &larr; {{ __('Manage Roles') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div class="p-4 bg-indigo-50 border border-indigo-200 text-indigo-900 rounded-lg text-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-indigo-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <strong class="font-semibold">{{ __('Read-Only Permission Matrix:') }}</strong>
                    {{ __('This matrix provides a bird\'s-eye view of all permissions registered in the system. Permission assignment is managed on a per-role basis via Role Edit.') }}
                </div>
            </div>

            @foreach ($groupedPermissions as $domainKey => $group)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <h3 class="font-semibold text-base text-gray-900">
                            {{ $group['title'] }}
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-medium text-xs">
                                <tr>
                                    <th scope="col" class="px-6 py-3 w-1/3">{{ __('Permission') }}</th>
                                    @foreach ($roles as $role)
                                        <th scope="col" class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ in_array($role->name, ['admin', 'author', 'user']) ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-700' }}">
                                                {{ $role->name }}
                                            </span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($group['permissions'] as $permission)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 font-mono text-xs text-gray-800 whitespace-nowrap">
                                            {{ $permission->name }}
                                        </td>
                                        @foreach ($roles as $role)
                                            @php
                                                $hasPermission = $role->permissions->contains('id', $permission->id);
                                            @endphp
                                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                                @if ($hasPermission)
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-600">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                        </svg>
                                                    </span>
                                                @else
                                                    <span class="text-gray-300 font-mono text-base">&ndash;</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
