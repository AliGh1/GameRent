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
            'image_url' => $this->image_url,
            'release_date' => $this->release_date,
            'age_rating' => $this->age_rating,
            'genres' => $this->genres->pluck('name'),
            'platforms' => $this->platforms->pluck('name'),
            'availability' => [
                'online' => $this->checkOnlineAvailability(),
                'online_offline' => $this->checkOnlineOfflineAvailability()
            ],
            'price' => [
                'online' => [
                    'one_week' => $this->calculatePrice(1, AccountMode::Online),
                    'two_week' => $this->calculatePrice(2, AccountMode::Online),
                    'three_week' => $this->calculatePrice(3, AccountMode::Online),
                    'one_month' => $this->calculatePrice(4, AccountMode::Online),
                ],
                'online_offline' => [
                    'one_week' => $this->calculatePrice(1, AccountMode::OnlineOffline),
                    'two_week' => $this->calculatePrice(2, AccountMode::OnlineOffline),
                    'three_week' => $this->calculatePrice(3, AccountMode::OnlineOffline),
                    'one_month' => $this->calculatePrice(4, AccountMode::OnlineOffline),
                ],
            ]
        ];
    }
}
