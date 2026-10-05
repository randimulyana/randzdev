<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Post') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.posts.store') }}" enctype="multipart/form-data" class="space-y-6">
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
                                <x-input-label for="type" :value="__('Type')" />
                                <select id="type" name="type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="article" @selected(old('type', 'article') === 'article')>{{ __('Article') }}</option>
                                    <option value="tutorial" @selected(old('type') === 'tutorial')>{{ __('Tutorial') }}</option>
                                    <option value="note" @selected(old('type') === 'note')>{{ __('Note') }}</option>
                                    <option value="guide" @selected(old('type') === 'guide')>{{ __('Guide') }}</option>
                                    <option value="case_study" @selected(old('type') === 'case_study')>{{ __('Case Study') }}</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('type')" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="draft" @selected(old('status', 'draft') === 'draft')>{{ __('Draft') }}</option>
                                    <option value="published" @selected(old('status') === 'published')>{{ __('Published') }}</option>
                                    <option value="archived" @selected(old('status') === 'archived')>{{ __('Archived') }}</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('status')" />
                            </div>

                            <div>
                                <x-input-label for="published_at" :value="__('Published At')" />
                                <x-text-input id="published_at" name="published_at" type="datetime-local" class="mt-1 block w-full" :value="old('published_at')" />
                                <x-input-error class="mt-2" :messages="$errors->get('published_at')" />
                            </div>
                        </div>

                        @if (auth()->user()?->hasRole('admin'))
                            <div>
                                <x-input-label for="author_id" :value="__('Author')" />
                                <select id="author_id" name="author_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach ($authors as $author)
                                        <option value="{{ $author->id }}" @selected(old('author_id', auth()->id()) == $author->id)>
                                            {{ $author->name }} ({{ $author->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('author_id')" />
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="featured_image_file" :value="__('Upload Featured Image')" />
                                <input id="featured_image_file" name="featured_image_file" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Supports JPG, PNG, WEBP, GIF up to 2MB. Takes precedence over URL.') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('featured_image_file')" />

                                <div class="mt-3">
                                    <x-input-label for="featured_image" :value="__('Or Image URL / Path')" />
                                    <x-text-input id="featured_image" name="featured_image" type="text" class="mt-1 block w-full" :value="old('featured_image')" placeholder="e.g. /images/posts/cover.jpg" />
                                    <x-input-error class="mt-2" :messages="$errors->get('featured_image')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="reading_time" :value="__('Reading Time (minutes)')" />
                                <x-text-input id="reading_time" name="reading_time" type="number" min="1" class="mt-1 block w-full" :value="old('reading_time')" placeholder="{{ __('Leave blank to auto-calculate') }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('reading_time')" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="excerpt" :value="__('Excerpt')" />
                            <textarea id="excerpt" name="excerpt" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('excerpt') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('excerpt')" />
                        </div>

                        <div>
                            <x-input-label for="content" :value="__('Content')" />
                            <textarea id="content" name="content" rows="10" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('content') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('content')" />
                        </div>

                        <div>
                            <span class="block font-medium text-sm text-gray-700 mb-2">{{ __('Tags') }}</span>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 p-4 border border-gray-200 rounded-md bg-gray-50 max-h-56 overflow-y-auto">
                                @forelse ($tags as $tag)
                                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700 cursor-pointer">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                            @checked(in_array($tag->id, old('tags', [])))
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span>{{ $tag->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-500 col-span-full">{{ __('No tags available. Please create tags first.') }}</p>
                                @endforelse
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('tags')" />
                        </div>

                        <div class="border-t border-gray-200 pt-6 space-y-6">
                            <h3 class="font-medium text-gray-900 text-sm uppercase tracking-wider">{{ __('SEO & Metadata (Optional)') }}</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="meta_title" :value="__('Meta Title')" />
                                    <x-text-input id="meta_title" name="meta_title" type="text" class="mt-1 block w-full" :value="old('meta_title')" />
                                    <x-input-error class="mt-2" :messages="$errors->get('meta_title')" />
                                </div>

                                <div>
                                    <x-input-label for="canonical_url" :value="__('Canonical URL')" />
                                    <x-text-input id="canonical_url" name="canonical_url" type="url" class="mt-1 block w-full" :value="old('canonical_url')" placeholder="https://..." />
                                    <x-input-error class="mt-2" :messages="$errors->get('canonical_url')" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="meta_description" :value="__('Meta Description')" />
                                <textarea id="meta_description" name="meta_description" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('meta_description') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('meta_description')" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save Post') }}</x-primary-button>
                            <a href="{{ route('admin.posts.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
