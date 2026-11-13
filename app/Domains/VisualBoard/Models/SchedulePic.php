<?php

namespace App\Domains\VisualBoard\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Database\Factories\SchedulePicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulePic extends Model
{
    /** @use HasFactory<SchedulePicFactory> */
    use HasFactory;

    use HasUlid;

    protected $fillable = [
        'monthly_schedule_id',
        'date',
        'pic_id',
        'pic_role',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * @return BelongsTo<MonthlySchedule, $this>
     */
    public function monthlySchedule(): BelongsTo
    {
        return $this->belongsTo(MonthlySchedule::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}
