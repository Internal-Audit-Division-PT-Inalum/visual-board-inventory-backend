<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'manager_id' => 'nullable|string|exists:users,id',
            'is_active' => 'boolean',
        ];
    }
}
