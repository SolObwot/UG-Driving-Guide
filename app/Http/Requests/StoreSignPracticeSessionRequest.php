<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSignPracticeSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare user input before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('sign_type') === 'all') {
            $this->merge(['sign_type' => null]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sign_type' => [
                'nullable',
                Rule::in([
                    'warning',
                    'regulatory',
                    'road_markings',
                    'information',
                    'road_signs',
                    'traffic_signals',
                    'hand_signals',
                    'other',
                ]),
            ],
            'total_questions' => ['required', 'integer', 'min:3', 'max:30'],
        ];
    }
}
