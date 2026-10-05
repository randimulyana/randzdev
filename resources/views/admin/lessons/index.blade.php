<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <nav class="flex items-center text-xs font-medium text-gray-500 space-x-2 mb-1">
                    <a href="{{ route('admin.learning-paths.index') }}" class="hover:text-indigo-600 transition">
                        {{ __('Learning Paths') }}
                    </a>
                    <span>/</span>
                    <a href="{{ route('admin.learning-paths.edit', $learningPath) }}" class="hover:text-indigo-600 transition truncate max-w-xs sm:max-w-md">
                        {{ $learningPath->title }}
                    </a>
                    <span>/</span>
                    <span class="text-gray-700 font-semibold">{{ __('Lessons') }}</span>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Manage Lessons') }}: {{ $learningPath->title }}
                </h2>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.learning-paths.edit', $learningPath) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    &larr; {{ __('Back to Learning Path') }}
                </a>
                @can('create', [App\Models\Lesson::class, $learningPath])
                    <a href="{{ route('admin.learning-paths.lessons.create', $learningPath) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        {{ __('+ Add Lesson') }}
                    </a>
                @endcan
            </div>
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
                <div class="p-6 text-gray-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Curriculum Lessons') }}</h3>
                            <p class="text-xs text-gray-500">{{ __('Lessons are ordered ascending by sequence order.') }}</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                            <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider font-medium text-xs">
                                <tr>
                                    <th scope="col" class="px-4 py-3">{{ __('Order') }}</th>
                                    <th scope="col" class="px-6 py-3">{{ __('Title') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Status') }}</th>
                                    <th scope="col" class="px-4 py-3">{{ __('Published At') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse ($lessons as $index => $lesson)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs font-mono font-bold text-gray-700 w-6">#{{ $lesson->order }}</span>
                                                <div class="flex flex-col space-y-0.5">
                                                    @can('update', $lesson)
                                                        @if ($index > 0)
                                                            <form action="{{ route('admin.learning-paths.lessons.move-up', [$learningPath, $lesson]) }}" method="POST" class="inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" title="{{ __('Move Up') }}" class="text-gray-400 hover:text-indigo-600 leading-none text-xs">
                                                                    ▲
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-gray-200 leading-none text-xs select-none">▲</span>
                                                        @endif
                                                        @if ($index < $lessons->count() - 1)
                                                            <form action="{{ route('admin.learning-paths.lessons.move-down', [$learningPath, $lesson]) }}" method="POST" class="inline">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" title="{{ __('Move Down') }}" class="text-gray-400 hover:text-indigo-600 leading-none text-xs">
                                                                    ▼
                                                                </button>
                                                            </form>
                                                        @else
                                                            <span class="text-gray-200 leading-none text-xs select-none">▼</span>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $lesson->title }}</div>
                                            <div class="text-xs text-gray-500 font-mono">{{ $lesson->slug }}</div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            @php
                                                $lessonStatusClasses = match ($lesson->status) {
                                                    'published' => 'bg-green-100 text-green-800',
                                                    'draft' => 'bg-gray-100 text-gray-800',
                                                    'archived' => 'bg-red-100 text-red-800',
                                                    default => 'bg-blue-100 text-blue-800',
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $lessonStatusClasses }}">
                                                {{ ucfirst($lesson->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-gray-500 whitespace-nowrap text-xs">
                                            {{ $lesson->published_at?->format('Y-m-d H:i') ?? __('Draft') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                            @if ($lesson->status === 'published')
                                                @can('unpublish', $lesson)
                                                    <form action="{{ route('admin.learning-paths.lessons.unpublish', [$learningPath, $lesson]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-amber-600 hover:text-amber-900 font-medium text-xs">
                                                            {{ __('Unpublish') }}
                                                        </button>
                                                    </form>
                                                @endcan
                                            @else
                                                @can('publish', $lesson)
                                                    <form action="{{ route('admin.learning-paths.lessons.publish', [$learningPath, $lesson]) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="text-emerald-600 hover:text-emerald-900 font-medium text-xs">
                                                            {{ __('Publish') }}
                                                        </button>
                                                    </form>
                                                @endcan
                                            @endif

                                            @can('update', $lesson)
                                                <a href="{{ route('admin.learning-paths.lessons.edit', [$learningPath, $lesson]) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">
                                                    {{ __('Edit') }}
                                                </a>
                                            @endcan

                                            @can('delete', $lesson)
                                                <form action="{{ route('admin.learning-paths.lessons.destroy', [$learningPath, $lesson]) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this lesson?') }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            {{ __('No lessons added yet. Click "+ Add Lesson" to create the first lesson.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
