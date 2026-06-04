<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class UpdateEditTagsRequest extends FormRequest
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $taxonomyPayload = [];

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
            'taxonomy' => ['required', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (ValidationValidator $validator): void {
                $decoded = json_decode((string) $this->input('taxonomy'), true);

                if (! is_array($decoded)) {
                    $validator->errors()->add('taxonomy', 'The taxonomy payload must be valid JSON.');

                    return;
                }

                $payloadValidator = Validator::make(['categories' => $decoded], [
                    'categories' => ['required', 'array'],
                    'categories.*.id' => ['nullable', 'integer'],
                    'categories.*.name' => ['required', 'string', 'max:255'],
                    'categories.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                    'categories.*.tags' => ['present', 'array'],
                    'categories.*.tags.*.id' => ['nullable', 'integer'],
                    'categories.*.tags.*.name' => ['required', 'string', 'max:255'],
                    'categories.*.tags.*.color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
                ]);

                if ($payloadValidator->fails()) {
                    foreach ($payloadValidator->errors()->all() as $message) {
                        $validator->errors()->add('taxonomy', $message);
                    }

                    return;
                }

                $this->taxonomyPayload = $decoded;
            },
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function taxonomyPayload(): array
    {
        return $this->taxonomyPayload;
    }
}
