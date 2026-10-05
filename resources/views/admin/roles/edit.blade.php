<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Role') }}: <span class="font-mono text-indigo-600">{{ $role->name }}</span>
            </h2>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                &larr; {{ __('Back to Roles') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-md text-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if ($role->name === 'admin')
                <div class="p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-md text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <strong class="font-semibold">{{ __('Admin Role Protected:') }}</strong>
                        {{ __('The admin role is the core system administrator role. Its permissions are locked and cannot be reduced or altered to prevent system lockout.') }}
                    </div>
                </div>
            @elseif ($isSystemRole)
                <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-md text-sm">
                    <strong class="font-semibold">{{ __('System Role:') }}</strong>
                    {{ __('This is a foundational system role. Its name cannot be changed, but you can adjust which permissions are assigned to it below.') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Role Name')" />
                            @if ($isSystemRole)
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-gray-100 text-gray-600 cursor-not-allowed" :value="$role->name" readonly />
                                <p class="text-xs text-gray-500 mt-1">{{ __('System role names cannot be renamed.') }}</p>
                            @else
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $role->name)" required autofocus />
                                <p class="text-xs text-gray-500 mt-1">{{ __('Letters, numbers, dashes, and underscores only.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            @endif
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ __('Permissions') }}</h3>
                                    <p class="text-xs text-gray-500">
                                        @if ($role->name === 'admin')
                                            {{ __('All permissions are granted and locked for the admin role.') }}
                                        @else
                                            {{ __('Configure permissions granted to users with this role.') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <x-input-error class="mb-4" :messages="$errors->get('permissions')" />
                            <x-input-error class="mb-4" :messages="$errors->get('permissions.*')" />

                            <div class="space-y-4">
                                @foreach ($groupedPermissions as $domainKey => $group)
                                    <div x-data="{
                                        toggleGroup(checked) {
                                            @if ($role->name !== 'admin')
                                                $el.querySelectorAll('.group-perm-cb').forEach(cb => {
                                                    cb.checked = checked;
                                                });
                                            @endif
                                        }
                                    }" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200">
                                            <h4 class="text-sm font-semibold text-gray-900">{{ $group['title'] }}</h4>
                                            @if ($role->name !== 'admin')
                                                <div class="flex items-center space-x-2">
                                                    <button type="button" @click="toggleGroup(true)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                        {{ __('Select All') }}
                                                    </button>
                                                    <span class="text-gray-300">|</span>
                                                    <button type="button" @click="toggleGroup(false)" class="text-xs text-gray-500 hover:text-gray-700 font-medium">
                                                        {{ __('Deselect All') }}
                                                    </button>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            @foreach ($group['permissions'] as $permission)
                                                <label class="inline-flex items-center text-sm text-gray-700 {{ $role->name === 'admin' ? 'cursor-not-allowed opacity-75' : 'cursor-pointer' }}">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $rolePermissionNames))) @disabled($role->name === 'admin') class="group-perm-cb rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                                                    <span class="font-mono text-xs">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                            @if ($role->name === 'admin')
                                <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Return to Roles') }}
                                </a>
                            @else
                                <x-primary-button>{{ __('Update Role') }}</x-primary-button>
                                <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Cancel') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
