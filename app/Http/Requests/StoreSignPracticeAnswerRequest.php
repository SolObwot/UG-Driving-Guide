<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSignPracticeAnswerRequest extends FormRequest
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
            'road_sign_id' => ['required', 'integer', 'exists:road_signs,id'],
            'selected_road_sign_id' => ['nullable', 'integer', 'exists:road_signs,id'],
            'time_taken_seconds' => ['nullable', 'integer', 'min:0', 'max:1800'],
        ];
    }
}
