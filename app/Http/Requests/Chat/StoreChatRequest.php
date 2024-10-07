<?php

namespace App\Http\Requests\Chat;

use App\Enums\ChatType;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreChatRequest extends FormRequest
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
            'type' => ['required', Rule::enum(ChatType::class)],
            'name' => [
                'required_if:type,' . ChatType::GROUP->value,
                'string',
                'max:100',
            ],
            'avatar' => [
                'nullable',
                File::image()
                    ->max('2mb')
                    ->dimensions(Rule::dimensions()->maxWidth(3000)->maxHeight(3000)),
            ],
            'description' => ['nullable', 'string', 'max:300'],
            'participants' => [
                'array',
                $this->input('type') == ChatType::GROUP->value
                    ? 'min:2' // group at least total participant is 2 (+1 initiated user)
                    : 'size:1', // private should only 1 (+1 initiated user)
            ],
            'participants.*' => [
                Rule::exists(User::class, 'id'),
                Rule::notIn([$this->user()->id]), // cannot add itself
            ],
        ];

        if ($this->filled('message')) {
            $messageRequest = new StoreMessageRequest;
            $messageRequest->merge($this->input('message', []));
            $messageRules = $messageRequest->rules();
            foreach ($messageRules as $key => $rule) {
                $rules["message.{$key}"] = $rule;
            }
        } else {
            $rules['message'] = ['nullable'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name' => [
                'required_if' => 'The name field is required when type is group.',
            ],
            'participants.*' => [
                'not_in' => 'Cannot select yourself as other participant.',
            ],
        ];
    }
}
