<?php

namespace App\Domains\VisualBoard\Models;

use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GeneralDocument extends Model implements HasMedia
{
    use HasFactory, HasUlid, InteractsWithMedia;

    protected $fillable = [
        'domain',
        'title',
        'description',
        'category',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: dokumen milik Tab GENERAL (Visual Board 5R — Basic Rule, Flow Process, Kaizen Report)
     */
    public function scopeVisualBoard(Builder $query): Builder
    {
        return $query->where('domain', 'visual_board');
    }

    /**
     * Scope: dokumen milik Tab ORGANISASI (Bagan Struktur, Map Area 5R)
     */
    public function scopeOrganization(Builder $query): Builder
    {
        return $query->where('domain', 'organization');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('document')
            ->singleFile()
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/webp',
            ]);
    }
}
