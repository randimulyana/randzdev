<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Technologies') }}
            </h2>
            @can('create', App\Models\Technology::class)
                <a href="{{ route('admin.technologies.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Create Technology') }}
                </a>
            @endcan
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
                                    <th scope="col" class="px-6 py-3">{{ __('Icon') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Name') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Slug') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Projects') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($technologies as $technology)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($technology->icon)
                                                <span class="inline-flex items-center justify-center h-8 w-8 rounded bg-gray-100 text-gray-700 font-mono text-xs">
                                                    {{ Str::limit($technology->icon, 6) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $technology->name }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap font-mono text-xs">
                                            {{ $technology->slug }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $technology->projects_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                            @can('update', $technology)
                                                <a href="{{ route('admin.technologies.edit', $technology) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    {{ __('Edit') }}
                                                </a>
                                            @endcan
                                            @can('delete', $technology)
                                                <form action="{{ route('admin.technologies.destroy', $technology) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this technology?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('No technologies found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($technologies->hasPages())
                        <div class="mt-4">
                            {{ $technologies->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
