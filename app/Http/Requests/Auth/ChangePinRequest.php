<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['required', 'string'],
            'new_pin' => ['required', 'string', 'min:6', 'max:6', 'confirmed'],
        ];
    }
}
