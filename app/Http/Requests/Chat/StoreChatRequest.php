<?php

namespace App\Http\Requests\Chat;

use App\Enums\ChatType;
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
        return [
            'type' => ['required', Rule::enum(ChatType::class)],
            'name' => ['nullable', 'string', 'max:100'],
            'avatar' => [
                'nullable',
                'string',
                File::image()
                    ->max('2mb')
                    ->dimensions(Rule::dimensions()->maxWidth(3000)->maxHeight(3000)),
            ],
            'description' => ['nullable', 'string', 'max:300'],
            'participants' => [
                'nullable', 'array',
                $this->input('type') == ChatType::GROUP->value
                    ? 'min:2' // group at least total participant is 2 (+1 initiated user)
                    : 'size:1', // private should only 1 (+1 initiated user)
                Rule::notIn([auth()->user()->id]), // cannot add itself
            ],
            'participants.*' => [Rule::exists(User::class, 'id')],
        ];
    }

    public function messages()
    {
        return [
            'not_in' => 'Cannot select yourself as other participant.',
        ];
    }
}
