<?php

namespace App\Http\Requests\VisualBoard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'day' => ['required', 'integer', 'min:1', 'max:31'],
            'status' => ['required', 'string', 'in:rencana,ok_tanpa_5r,ok_dengan_5r,abnormal,libur'],
        ];
    }
}
