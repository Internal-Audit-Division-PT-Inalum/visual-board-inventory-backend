<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\HR\Models\Employee;
use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string $id
 * @property string $name
 * @property string $area
 * @property string|null $pic_utama_id
 * @property string|null $pic_pengganti_id
 * @property-read Employee|null $picUtama
 * @property-read Employee|null $picPengganti
 */
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

    protected $appends = ['standard_image_url'];

    public function getStandardImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('standard_images') ?: null;
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function picUtama(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pic_utama_id');
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function picPengganti(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'pic_pengganti_id');
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
            ->singleFile()
            ->useDisk('public');
    }
}
