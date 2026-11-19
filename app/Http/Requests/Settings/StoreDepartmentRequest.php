<?php

namespace App\Http\Requests\Settings;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Department::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:departments,code'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
