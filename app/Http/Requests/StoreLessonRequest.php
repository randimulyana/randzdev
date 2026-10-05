<?php

namespace App\Http\Requests;

use App\Models\Lesson;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $learningPath = $this->route('learning_path');

        return $this->user()?->can('create', [Lesson::class, $learningPath]) ?? false;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->filled('order') && $this->route('learning_path')) {
            $nextOrder = (int) ($this->route('learning_path')->lessons()->max('order') ?? 0) + 1;
            $this->merge(['order' => $nextOrder]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $learningPathId = $this->route('learning_path')?->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('lessons', 'slug')->where('learning_path_id', $learningPathId),
            ],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'order' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
