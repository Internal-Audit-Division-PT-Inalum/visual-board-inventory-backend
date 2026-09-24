<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class ScanAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'namecode' => ['required', 'string'],
            'status' => ['required', 'string', 'in:present,business_trip,leave'],
        ];
    }
}
