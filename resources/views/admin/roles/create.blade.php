<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Role') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Role Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus placeholder="e.g. editor, moderator" />
                            <p class="text-xs text-gray-500 mt-1">{{ __('Letters, numbers, dashes, and underscores only.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ __('Assign Permissions') }}</h3>
                                    <p class="text-xs text-gray-500">{{ __('Select the permissions granted to this role.') }}</p>
                                </div>
                            </div>

                            <x-input-error class="mb-4" :messages="$errors->get('permissions')" />
                            <x-input-error class="mb-4" :messages="$errors->get('permissions.*')" />

                            <div class="space-y-4">
                                @foreach ($groupedPermissions as $domainKey => $group)
                                    <div x-data="{
                                        toggleGroup(checked) {
                                            $el.querySelectorAll('.group-perm-cb').forEach(cb => {
                                                cb.checked = checked;
                                            });
                                        }
                                    }" class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200">
                                            <h4 class="text-sm font-semibold text-gray-900">{{ $group['title'] }}</h4>
                                            <div class="flex items-center space-x-2">
                                                <button type="button" @click="toggleGroup(true)" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                                    {{ __('Select All') }}
                                                </button>
                                                <span class="text-gray-300">|</span>
                                                <button type="button" @click="toggleGroup(false)" class="text-xs text-gray-500 hover:text-gray-700 font-medium">
                                                    {{ __('Deselect All') }}
                                                </button>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            @foreach ($group['permissions'] as $permission)
                                                <label class="inline-flex items-center text-sm text-gray-700 cursor-pointer">
                                                    <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', []))) class="group-perm-cb rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                                                    <span class="font-mono text-xs">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                            <x-primary-button>{{ __('Save Role') }}</x-primary-button>
                            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
