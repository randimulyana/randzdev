<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Posts') }}
            </h2>
            @can('create', App\Models\Post::class)
                <a href="{{ route('admin.posts.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Create Post') }}
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
                                    <th scope="col" class="px-6 py-3">{{ __('Title') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Author') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Category') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Tags') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Type') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Published At') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($posts as $post)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $post->title }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $post->slug }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap text-xs">
                                            {{ $post->author?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap">
                                            {{ $post->category?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                @forelse ($post->tags as $tag)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        {{ $tag->name }}
                                                    </span>
                                                @empty
                                                    <span class="text-gray-400 text-xs">-</span>
                                                @endforelse
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ ucfirst(str_replace('_', ' ', $post->type)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClasses = match ($post->status) {
                                                    'published' => 'bg-green-100 text-green-800',
                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                    'archived' => 'bg-red-100 text-red-800',
                                                    default => 'bg-blue-100 text-blue-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                                {{ ucfirst($post->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap text-xs">
                                            {{ $post->published_at?->format('Y-m-d H:i') ?? __('Draft') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                            @can('update', $post)
                                                <a href="{{ route('admin.posts.edit', $post) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    {{ __('Edit') }}
                                                </a>
                                            @endcan
                                            @can('delete', $post)
                                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this post?') }}');">
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
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('No posts found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($posts->hasPages())
                        <div class="mt-4">
                            {{ $posts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
