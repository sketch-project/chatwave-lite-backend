<?php

namespace App\Http\Requests\Message;

use App\Enums\MessageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreMessageRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'reply_id' => ['nullable', 'ulid'],
            'message_type' => ['required', Rule::enum(MessageType::class)],
            'content' => 'required|string|max:2000',
            'is_forwarded' => ['boolean'],
        ];
        if ($this->input('message_type') != MessageType::TEXT->value) {
            $rules['media'] = [
                'required_without:media_base64',
                $this->input('message_type') == MessageType::IMAGE->value
                    ? File::image()
                        ->max('2mb')
                        ->dimensions(Rule::dimensions()->maxWidth(3000)->maxHeight(3000))
                        : File::types([
                            'pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx',
                            'zip', 'rar', 'jpg', 'jpeg', 'gif', 'png',
                        ])
                            ->max('5mb'),
            ];
            $rules['media_base64'] = [
                'nullable',
                'required_without:media',
                function ($attribute, $value, $fail) {
                    $base64 = $value;
                    if (preg_match('/^data:(\w+\/\w+);base64,/', $value)) {
                        $base64 = preg_replace('/^data:(\w+\/\w+);base64,/', '', $value);
                    }
                    if (!base64_decode($base64, true)) {
                        $fail(__(':attribute is not a valid base64 encoded string.', [
                            'attribute' => $attribute,
                        ]));
                    }
                },
            ];
        }

        return $rules;
    }
}
