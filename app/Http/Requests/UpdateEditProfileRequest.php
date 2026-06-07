<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateEditProfileRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
            ],
            'user_dob' => ['nullable', 'date', 'before_or_equal:today'],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'user_bio' => ['nullable', 'string', 'max:260'],
            'cropped_avatar' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $avatar = (string) $this->input('cropped_avatar', '');

                if ($avatar === '') {
                    return;
                }

                if (! preg_match('/^data:image\/(png|jpe?g|gif);base64,/', $avatar, $matches)) {
                    $validator->errors()->add('cropped_avatar', 'The profile image must be a PNG, JPG, or GIF image.');

                    return;
                }

                $base64 = substr($avatar, strpos($avatar, ',') + 1);
                $decoded = base64_decode($base64, true);

                if ($decoded === false) {
                    $validator->errors()->add('cropped_avatar', 'The profile image could not be decoded.');

                    return;
                }

                $maxBytes = 1 * 1024 * 1024; // 1MB

                if (strlen($decoded) > $maxBytes) {
                    $validator->errors()->add('cropped_avatar', 'The profile image must be smaller than 1MB.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_dob' => 'date of birth',
            'user_phone' => 'phone number',
            'user_bio' => 'bio',
            'cropped_avatar' => 'profile image',
        ];
    }
}
