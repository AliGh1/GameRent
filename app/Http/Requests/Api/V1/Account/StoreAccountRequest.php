<?php

namespace App\Http\Requests\Api\V1\Account;

use App\Enums\AccountMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create.account');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('accounts')->where(function ($query) {
                    return $query->where(
                        [
                            ["email", $this->input('email')],
                            ["mode", $this->input('mode')]
                        ]
                    );
                }),
            ],
            'password' => 'required|string|max:64',
            'secret_key' => 'required|string|max:64',
            'mode' => ['required', Rule::enum(AccountMode::class)],
        ];
    }
}
