<?php

namespace App\Domains\HR\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string|null $department_id
 * @property string|null $position_id
 * @property string $name
 * @property string $namecode
 * @property-read User|null $user
 */
class Employee extends Model implements HasMedia
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    use HasUlid, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'employee_code',
        'name',
        'namecode',
        'position_title',
        'hierarchy_level',
        'is_active',
        'pin',
        'pin_set_at',
        'must_change_pin',
        'pin_failed_attempts',
        'pin_locked_until',
    ];

    protected $casts = [
        'hierarchy_level' => 'integer',
        'is_active' => 'boolean',
        'pin_set_at' => 'datetime',
        'must_change_pin' => 'boolean',
        'pin_failed_attempts' => 'integer',
        'pin_locked_until' => 'datetime',
    ];

    protected $hidden = [
        'pin',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Position, $this>
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * @return HasMany<EmployeeAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(EmployeeAttendance::class);
    }

    protected static function booted(): void
    {
        static::saving(function (Employee $employee) {
            if ($employee->isDirty('position_id') && $employee->position_id) {
                $position = Position::find($employee->position_id);
                if ($position) {
                    $employee->position_title = $position->name;
                    $employee->hierarchy_level = $position->hierarchy_level;
                    $employee->setRelation('position', $position);
                }
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useDisk('public');
    }
}
