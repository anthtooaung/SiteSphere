<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StorePostsRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:http,https', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'tags' => ['required', 'array', 'min:1'],
            'tags.*' => ['integer', Rule::exists('tags', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'url' => 'website link',
            'tags' => 'tags',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => Str::of((string) $this->input('title'))->trim()->toString(),
            'url' => Str::of((string) $this->input('url'))->trim()->toString(),
            'description' => Str::of((string) $this->input('description'))->trim()->toString(),
            'tags' => collect($this->input('tags', []))
                ->filter(fn (mixed $tag): bool => $tag !== null && $tag !== '')
                ->unique()
                ->values()
                ->all(),
        ]);
    }
}
