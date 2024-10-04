<?php

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
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
        $user = $this->user();
        $uniqueUsernameRule = Rule::unique(User::class, 'username')->ignore($user);
        $uniqueEmailRule = Rule::unique(User::class, 'email')->ignore($user);
        $uniquePhoneRule = Rule::unique(User::class, 'phone_number')->ignore($user);

        return [
            'name' => 'required|max:100',
            'username' => ['required', 'min:3', 'max:30', "regex:/^[a-zA-Z0-9_\-.]+$/", $uniqueUsernameRule],
            'email' => ['required', 'max:50', $uniqueEmailRule],
            'phone_number' => ['required', 'max:20', $uniquePhoneRule],
            'avatar' => [
                'nullable',
                File::image()
                    ->max('2mb')
                    ->dimensions(Rule::dimensions()->maxWidth(3000)->maxHeight(3000)),
            ],
            'password' => [
                'nullable',
                'string',
                'confirmed',
                Password::min(5)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'Username only alphanumeric dash and period',
        ];
    }
}
