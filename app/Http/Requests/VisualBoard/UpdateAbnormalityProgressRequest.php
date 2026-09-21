<?php

namespace App\Http\Requests\VisualBoard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbnormalityProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'countermeasure_actual' => ['nullable', 'string', 'max:1000'],
            'pic_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
