<?php

namespace App\Http\Requests\VisualBoard;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAbnormalityRequest extends FormRequest
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
            'zone_id' => ['required', 'exists:zones,id'],
            'monthly_schedule_id' => ['nullable', 'exists:monthly_schedules,id'],
            'inspection_criteria_id' => ['nullable', 'exists:inspection_criteria,id'],
            'date_found' => ['required', 'date'],
            'problem_description' => ['required', 'string', 'max:1000'],
            'countermeasure_plan' => ['nullable', 'string', 'max:1000'],
            'is_kaizen' => ['nullable', 'boolean'],
        ];
    }
}
