<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('roles.update') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Role|string|int|null $role */
        $role = $this->route('role');
        $roleModel = $role instanceof Role ? $role : Role::findById((int) $role);
        $isSystemRole = $roleModel && in_array($roleModel->name, ['admin', 'author', 'user'], true);

        $rules = [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions', 'name')],
        ];

        if (! $isSystemRole) {
            $rules['name'] = [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_\-]+$/',
                Rule::unique('roles', 'name')->ignore($roleModel?->id),
            ];
        }

        return $rules;
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The role name may only contain letters, numbers, dashes, and underscores.',
            'permissions.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }
}
