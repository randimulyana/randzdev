<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tags') }}
            </h2>
            @can('create', App\Models\Tag::class)
                <a href="{{ route('admin.tags.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Create Tag') }}
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
                                    <th scope="col" class="px-6 py-3">{{ __('Name') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Slug') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Posts') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($tags as $tag)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $tag->name }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap font-mono text-xs">
                                            {{ $tag->slug }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $tag->posts_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                            @can('update', $tag)
                                                <a href="{{ route('admin.tags.edit', $tag) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    {{ __('Edit') }}
                                                </a>
                                            @endcan
                                            @can('delete', $tag)
                                                <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this tag?') }}');">
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
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('No tags found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($tags->hasPages())
                        <div class="mt-4">
                            {{ $tags->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
