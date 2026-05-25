<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreContactMessageRequest extends FormRequest
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
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => Str::of($this->input('first_name', ''))->trim()->toString(),
            'last_name' => Str::of($this->input('last_name', ''))->trim()->toString(),
            'email' => Str::of($this->input('email', ''))->trim()->lower()->toString(),
            'message' => Str::of($this->input('message', ''))->trim()->toString(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return route('welcome', ['scroll' => 'contact']);
    }
}
