<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', Password::min(12)->letters()->numbers()],
            'role_ids' => ['present', 'array'],
            'role_ids.*' => [Rule::exists('roles', 'id')],
            'department_ids' => ['present', 'array'],
            'department_ids.*' => [Rule::exists('departments', 'id')],
        ];
    }
}
