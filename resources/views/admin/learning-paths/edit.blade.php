<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Learning Path') }}: {{ $learningPath->title }}
            </h2>
            @can('create', [App\Models\Lesson::class, $learningPath])
                <a href="{{ route('admin.learning-paths.lessons.create', $learningPath) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    {{ __('+ Add Lesson') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-md text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Learning Path Edit Form --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.learning-paths.update', $learningPath) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $learningPath->title)" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            <div>
                                <x-input-label for="slug" :value="__('Slug')" />
                                <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $learningPath->slug)" placeholder="{{ __('Leave blank to keep existing slug') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="difficulty" :value="__('Difficulty')" />
                                <select id="difficulty" name="difficulty" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="beginner" @selected(old('difficulty', $learningPath->difficulty) === 'beginner')>{{ __('Beginner') }}</option>
                                    <option value="intermediate" @selected(old('difficulty', $learningPath->difficulty) === 'intermediate')>{{ __('Intermediate') }}</option>
                                    <option value="advanced" @selected(old('difficulty', $learningPath->difficulty) === 'advanced')>{{ __('Advanced') }}</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('difficulty')" />
                            </div>

                            <div>
                                <x-input-label for="estimated_duration" :value="__('Estimated Duration')" />
                                <x-text-input id="estimated_duration" name="estimated_duration" type="text" class="mt-1 block w-full" :value="old('estimated_duration', $learningPath->estimated_duration)" placeholder="e.g. 4 hours, 2 weeks" />
                                <x-input-error class="mt-2" :messages="$errors->get('estimated_duration')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="draft" @selected(old('status', $learningPath->status) === 'draft')>{{ __('Draft') }}</option>
                                    <option value="published" @selected(old('status', $learningPath->status) === 'published')>{{ __('Published') }}</option>
                                    <option value="archived" @selected(old('status', $learningPath->status) === 'archived')>{{ __('Archived') }}</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('status')" />
                            </div>

                            <div>
                                <x-input-label for="published_at" :value="__('Published At')" />
                                <x-text-input id="published_at" name="published_at" type="datetime-local" class="mt-1 block w-full" :value="old('published_at', $learningPath->published_at?->format('Y-m-d\TH:i'))" />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Leave empty when publishing to automatically use current time.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('published_at')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="thumbnail_file" :value="__('Upload New Thumbnail')" />
                            @if ($learningPath->thumbnail)
                                <div class="my-2 flex items-center space-x-3">
                                    <img src="{{ $learningPath->thumbnail }}" alt="{{ $learningPath->title }}" class="h-16 w-16 rounded object-cover border border-gray-200">
                                    <span class="text-xs text-gray-500 break-all">{{ $learningPath->thumbnail }}</span>
                                </div>
                            @endif
                            <input id="thumbnail_file" name="thumbnail_file" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Supports JPG, PNG, WEBP, GIF up to 2MB. Uploading a new file will replace the current thumbnail.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('thumbnail_file')" />

                            <div class="mt-3">
                                <x-input-label for="thumbnail" :value="__('Or Thumbnail URL / Path')" />
                                <x-text-input id="thumbnail" name="thumbnail" type="text" class="mt-1 block w-full" :value="old('thumbnail', $learningPath->thumbnail)" />
                                <x-input-error class="mt-2" :messages="$errors->get('thumbnail')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $learningPath->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="flex items-center justify-end space-x-4">
                            <a href="{{ route('admin.learning-paths.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Back to List') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Learning Path') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Lessons Table Section --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Lessons in this Learning Path') }}</h3>
                            <p class="text-xs text-gray-500">{{ __('Lessons are ordered by numerical sequence (ascending).') }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.learning-paths.lessons.index', $learningPath) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none transition">
                                {{ __('Manage Lessons') }}
                            </a>
                            @can('create', [App\Models\Lesson::class, $learningPath])
                                <a href="{{ route('admin.learning-paths.lessons.create', $learningPath) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none transition">
                                    {{ __('Add Lesson') }}
                                </a>
                            @endcan
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
                                @forelse ($learningPath->lessons as $index => $lesson)
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
                                                        @if ($index < $learningPath->lessons->count() - 1)
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
                                                <form action="{{ route('admin.learning-paths.lessons.destroy', [$learningPath, $lesson]) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this lesson?') }}');">
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
