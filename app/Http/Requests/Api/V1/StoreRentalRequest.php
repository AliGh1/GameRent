<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\AccountMode;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRentalRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'account_mode' => ['required', Rule::enum(AccountMode::class)],
            'rental_duration_weeks' => 'required|integer|min:1|max:4',
        ];
    }
}
