<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Learning Paths') }}
            </h2>
            @can('create', App\Models\LearningPath::class)
                <a href="{{ route('admin.learning-paths.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('Create Learning Path') }}
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
                                    <th scope="col" class="px-6 py-3">{{ __('Difficulty') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Duration') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Lessons') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Status') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Creator') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Published At') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($learningPaths as $learningPath)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $learningPath->title }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $learningPath->slug }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $difficultyClasses = match ($learningPath->difficulty) {
                                                    'beginner' => 'bg-emerald-100 text-emerald-800',
                                                    'intermediate' => 'bg-amber-100 text-amber-800',
                                                    'advanced' => 'bg-rose-100 text-rose-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $difficultyClasses }}">
                                                {{ ucfirst($learningPath->difficulty) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap text-xs">
                                            {{ $learningPath->estimated_duration ?: '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-gray-700">
                                            {{ $learningPath->lessons_count }} {{ __('lessons') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClasses = match ($learningPath->status) {
                                                    'published' => 'bg-green-100 text-green-800',
                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                    'archived' => 'bg-red-100 text-red-800',
                                                    default => 'bg-blue-100 text-blue-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses }}">
                                                {{ ucfirst($learningPath->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 whitespace-nowrap text-xs">
                                            {{ $learningPath->creator?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap text-xs">
                                            {{ $learningPath->published_at?->format('Y-m-d H:i') ?? __('Draft') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                            @can('update', $learningPath)
                                                <a href="{{ route('admin.learning-paths.edit', $learningPath) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                                    {{ __('Edit') }}
                                                </a>
                                            @endcan
                                            @can('delete', $learningPath)
                                                <form action="{{ route('admin.learning-paths.destroy', $learningPath) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this learning path?') }}');">
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
                                            {{ __('No learning paths found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($learningPaths->hasPages())
                        <div class="mt-4">
                            {{ $learningPaths->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
