<?php

namespace App\Http\Resources\Api\Portal;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BulletinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'author' => $this->whenLoaded('author', fn () => $this->author->name),
        ];
    }
}
