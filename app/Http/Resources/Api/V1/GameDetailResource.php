<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\AccountMode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class GameDetailResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'image_url' => $this->image_url,
            'release_date' => $this->release_date,
            'age_rating' => $this->age_rating,
            'genres' => $this->genres->pluck('name'),
            'platforms' => $this->platforms->pluck('name'),
            'availability' => [
                'online' => $this->checkAvailability(AccountMode::ONLINE),
                'online_offline' => $this->checkAvailability(AccountMode::ONLINE_OFFLINE),
            ],
            'price' => [
                'online' => [
                    'one_week' => $this->calculatePrice(1, AccountMode::ONLINE),
                    'two_week' => $this->calculatePrice(2, AccountMode::ONLINE),
                    'three_week' => $this->calculatePrice(3, AccountMode::ONLINE),
                    'one_month' => $this->calculatePrice(4, AccountMode::ONLINE),
                ],
                'online_offline' => [
                    'one_week' => $this->calculatePrice(1, AccountMode::ONLINE_OFFLINE),
                    'two_week' => $this->calculatePrice(2, AccountMode::ONLINE_OFFLINE),
                    'three_week' => $this->calculatePrice(3, AccountMode::ONLINE_OFFLINE),
                    'one_month' => $this->calculatePrice(4, AccountMode::ONLINE_OFFLINE),
                ],
            ]
        ];
    }
}
