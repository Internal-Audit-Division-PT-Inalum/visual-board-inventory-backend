<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|string|exists:users,id',
            'department_id' => 'required|string|exists:departments,id',
            'namecode' => 'required|string|unique:employees,namecode|max:50',
            'position_title' => 'required|string|max:255',
        ];
    }
}
