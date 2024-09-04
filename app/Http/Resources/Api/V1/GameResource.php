<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class GameResource extends JsonResource
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
            'description' => Str::limit($this->description),
            'slug' => $this->slug,
            'image_url' => $this->image_url,
            'weekly_online_price' => $this->weekly_online_price,
            'weekly_online_offline_price' => $this->weekly_online_offline_price,
        ];
    }
}
