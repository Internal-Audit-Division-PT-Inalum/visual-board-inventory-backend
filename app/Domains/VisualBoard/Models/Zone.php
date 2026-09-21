<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Zone extends Model implements HasMedia
{
    use HasFactory, HasUlid, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name',
        'area',
        'pic_utama_id',
        'pic_pengganti_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function picUtama(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_utama_id');
    }

    public function picPengganti(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_pengganti_id');
    }

    public function criterias(): HasMany
    {
        return $this->hasMany(InspectionCriteria::class);
    }

    public function workstations(): HasMany
    {
        return $this->hasMany(Workstation::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('standard_images')
            ->useDisk('public')
            ->useFallbackUrl('/images/default-zone.png');
    }
}
