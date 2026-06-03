<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppearanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dark_mode' => ['required', 'boolean'],
            'use_custom_theme' => ['required', 'boolean'],
            'theme_id' => [
                Rule::requiredIf(fn (): bool => ! $this->boolean('use_custom_theme')),
                'nullable',
                'integer',
                'exists:themes,id',
            ],
            'background_color' => [
                Rule::requiredIf(fn (): bool => $this->boolean('use_custom_theme')),
                'nullable',
                'hex_color',
            ],
            'text_color' => [
                Rule::requiredIf(fn (): bool => $this->boolean('use_custom_theme')),
                'nullable',
                'hex_color',
            ],
            'accent_color' => [
                Rule::requiredIf(fn (): bool => $this->boolean('use_custom_theme')),
                'nullable',
                'hex_color',
            ],
            'font_id' => ['required', 'integer', 'exists:fonts,id'],
            'menuBar_location' => ['required', Rule::in(['top', 'right', 'bottom', 'left'])],
            'noti_location' => ['required', Rule::in(['top-start', 'top-end', 'bottom-end', 'bottom-start'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'dark_mode' => $this->boolean('dark_mode'),
            'use_custom_theme' => $this->boolean('use_custom_theme'),
        ]);
    }
}
