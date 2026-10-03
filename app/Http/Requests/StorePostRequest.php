<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('posts.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $eligibleAuthorIds = User::role(['admin', 'author'])->pluck('id')->all();

        return [
            'author_id' => ['nullable', 'integer', Rule::in($eligibleAuthorIds)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('posts', 'slug')],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['article', 'tutorial', 'note', 'guide', 'case_study'])],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'published_at' => ['nullable', 'date'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'exists:tags,id'],
        ];
    }
}
