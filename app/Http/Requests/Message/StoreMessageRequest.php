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
        return [
            'reply_id' => ['nullable', 'ulid'],
            'message_type' => ['required', Rule::enum(MessageType::class)],
            'content' => 'required|string|max:2000',
            'media' => [
                Rule::requiredIf($this->input('message_type') != MessageType::TEXT->value),
                $this->input('message_type') == MessageType::IMAGE
                    ? File::image()
                        ->max('2mb')
                        ->dimensions(Rule::dimensions()->maxWidth(3000)->maxHeight(3000))
                    : File::types([
                        'pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx',
                        'zip', 'rar', 'jpg', 'jpeg', 'gif', 'png',
                    ])
                        ->max('5mb'),
            ],
            'is_forwarded' => ['boolean'],
        ];
    }
}
