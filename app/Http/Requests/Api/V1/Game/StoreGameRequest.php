<?php

namespace App\Http\Requests\Api\V1\Game;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('create.game');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:games,title',
            'description' => 'required|string',
            'release_date' => 'required|date',
            'age_rating' => 'required|string',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:1024',
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id',
            'platforms' => 'required|array',
            'platforms.*' => 'exists:platforms,id',
            'weekly_online_price' => 'required|integer|min:10000',
            'weekly_online_offline_price' => 'required|integer|min:10000',
        ];
    }
}
