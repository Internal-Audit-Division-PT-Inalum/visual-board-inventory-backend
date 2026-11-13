<?php

namespace App\Domains\VisualBoard\Models;

use App\Shared\Concerns\HasUlid;
use Database\Factories\InspectionCriteriaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InspectionCriteria extends Model
{
    /** @use HasFactory<InspectionCriteriaFactory> */
    use HasFactory;

    use HasUlid, SoftDeletes;

    protected $fillable = [
        'zone_id',
        'item_group',
        'criteria_code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Zone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
