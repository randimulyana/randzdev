<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="title" :value="__('Title')" />
                                <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('title')" />
                            </div>

                            <div>
                                <x-input-label for="slug" :value="__('Slug')" />
                                <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug')" placeholder="{{ __('Leave blank to generate automatically') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="category_id" :value="__('Category')" />
                                <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">{{ __('None / Uncategorized') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                            </div>

                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="completed" @selected(old('status', 'completed') === 'completed')>{{ __('Completed') }}</option>
                                    <option value="in_progress" @selected(old('status') === 'in_progress')>{{ __('In Progress') }}</option>
                                    <option value="draft" @selected(old('status') === 'draft')>{{ __('Draft') }}</option>
                                    <option value="archived" @selected(old('status') === 'archived')>{{ __('Archived') }}</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('status')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="role" :value="__('Role')" />
                                <x-text-input id="role" name="role" type="text" class="mt-1 block w-full" :value="old('role')" placeholder="e.g. Full-Stack Developer" />
                                <x-input-error class="mt-2" :messages="$errors->get('role')" />
                            </div>

                            <div>
                                <x-input-label for="thumbnail_file" :value="__('Upload Thumbnail')" />
                                <input id="thumbnail_file" name="thumbnail_file" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Supports JPG, PNG, WEBP, GIF up to 2MB. Takes precedence over URL.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('thumbnail_file')" />

                                <div class="mt-3">
                                    <x-input-label for="thumbnail" :value="__('Or Thumbnail URL / Path')" />
                                    <x-text-input id="thumbnail" name="thumbnail" type="text" class="mt-1 block w-full" :value="old('thumbnail')" placeholder="e.g. /images/projects/portfolio.png" />
                                    <x-input-error class="mt-2" :messages="$errors->get('thumbnail')" />
                                </div>
                            </div>
                        </div>

                        <div>
                            <x-input-label for="short_description" :value="__('Short Description')" />
                            <textarea id="short_description" name="short_description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('short_description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('short_description')" />
                        </div>

                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="5" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="repository_url" :value="__('Repository URL')" />
                                <x-text-input id="repository_url" name="repository_url" type="url" class="mt-1 block w-full" :value="old('repository_url')" placeholder="https://github.com/..." />
                                <x-input-error class="mt-2" :messages="$errors->get('repository_url')" />
                            </div>

                            <div>
                                <x-input-label for="live_url" :value="__('Live URL')" />
                                <x-text-input id="live_url" name="live_url" type="url" class="mt-1 block w-full" :value="old('live_url')" placeholder="https://example.com" />
                                <x-input-error class="mt-2" :messages="$errors->get('live_url')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="started_at" :value="__('Started At')" />
                                <x-text-input id="started_at" name="started_at" type="date" class="mt-1 block w-full" :value="old('started_at')" />
                                <x-input-error class="mt-2" :messages="$errors->get('started_at')" />
                            </div>

                            <div>
                                <x-input-label for="completed_at" :value="__('Completed At')" />
                                <x-text-input id="completed_at" name="completed_at" type="date" class="mt-1 block w-full" :value="old('completed_at')" />
                                <x-input-error class="mt-2" :messages="$errors->get('completed_at')" />
                            </div>

                            <div>
                                <x-input-label for="published_at" :value="__('Published At')" />
                                <x-text-input id="published_at" name="published_at" type="date" class="mt-1 block w-full" :value="old('published_at')" />
                                <x-input-error class="mt-2" :messages="$errors->get('published_at')" />
                            </div>
                        </div>

                        <div>
                            <span class="block font-medium text-sm text-gray-700 mb-2">{{ __('Technologies') }}</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 p-4 border border-gray-200 rounded-md bg-gray-50 max-h-56 overflow-y-auto">
                                @forelse ($technologies as $tech)
                                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                                        <input type="checkbox" name="technologies[]" value="{{ $tech->id }}"
                                            @checked(in_array($tech->id, old('technologies', [])))
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span>{{ $tech->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-500 col-span-full">{{ __('No technologies available. Please create technologies first.') }}</p>
                                @endforelse
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('technologies')" />
                        </div>

                        <div>
                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="featured" value="1" @checked(old('featured')) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700">{{ __('Feature this project on homepage / portfolio highlight') }}</span>
                            </label>
                            <x-input-error class="mt-2" :messages="$errors->get('featured')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save Project') }}</x-primary-button>
                            <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
