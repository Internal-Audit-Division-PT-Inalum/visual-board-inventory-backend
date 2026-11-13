<?php

namespace App\Http\Resources\Api\Portal;

use App\Domains\Portal\Models\Bulletin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bulletin
 */
class BulletinResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'image_url' => $this->image_url ? asset('storage/' . $this->image_url) : null,
            'document_url' => $this->document_url ? url('/api/v1/portal/kiosk/bulletins/' . $this->id . '/document') : null,
            'published_at' => $this->published_at ? $this->published_at->format('Y-m-d H:i:s') : $this->created_at?->format('Y-m-d H:i:s'),
            'expired_at' => $this->expired_at ? $this->expired_at->format('Y-m-d H:i:s') : null,
            'author' => $this->whenLoaded('author', fn () => $this->author?->name),
        ];
    }
}
