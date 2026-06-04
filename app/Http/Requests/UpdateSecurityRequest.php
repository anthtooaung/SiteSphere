<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateSecurityRequest extends FormRequest
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
            'two_factor_enabled' => ['nullable', 'boolean'],
            'user_post_visible' => ['nullable', 'boolean'],
            'current_password' => ['nullable', 'required_with:password,password_confirmation', 'current_password'],
            'password' => ['nullable', 'required_with:current_password', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'two_factor_enabled' => 'two-factor authentication',
            'user_post_visible' => 'posting visibility',
            'current_password' => 'current password',
            'password' => 'new password',
        ];
    }
}
