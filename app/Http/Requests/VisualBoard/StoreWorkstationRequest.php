<?php

namespace App\Http\Requests\VisualBoard;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkstationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zone_id' => 'required|string|exists:zones,id',
            'employee_id' => 'nullable|string|exists:employees,id',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
