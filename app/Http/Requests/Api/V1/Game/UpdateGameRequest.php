<?php

namespace App\Http\Requests\Api\V1\Game;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('edit.game');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255|unique:games,title,id',
            'description' => 'sometimes|string',
            'release_date' => 'sometimes|date',
            'age_rating' => 'sometimes|string',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png|max:1024',
            'genres' => 'sometimes|array',
            'genres.*' => 'exists:genres,id',
            'platforms' => 'sometimes|array',
            'platforms.*' => 'sometimes|exists:platforms,id',
            'weekly_online_price' => 'sometimes|integer|min:10000',
            'weekly_online_offline_price' => 'sometimes|integer|min:10000',
        ];
    }
}
