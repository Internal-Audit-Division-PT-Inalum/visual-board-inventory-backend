<?php

namespace App\Http\Requests\VisualBoard;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAbnormalityProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'progress_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'countermeasure_actual' => ['nullable', 'string', 'max:1000'],
            'pic_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
