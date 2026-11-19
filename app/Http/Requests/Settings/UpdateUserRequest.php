<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        /** @var User|null $target */
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($target?->id)],
            // Parola isteğe bağlı; verilirse en az 12 karakter.
            'password' => ['nullable', Password::min(12)->letters()->numbers()],
            'is_active' => ['required', 'boolean'],
            'role_ids' => ['present', 'array'],
            'role_ids.*' => [Rule::exists('roles', 'id')],
            'department_ids' => ['present', 'array'],
            'department_ids.*' => [Rule::exists('departments', 'id')],
        ];
    }
}
