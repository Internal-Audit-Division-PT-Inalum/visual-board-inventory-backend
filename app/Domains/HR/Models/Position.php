<?php

namespace App\Domains\HR\Models;

use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, HasUlid, SoftDeletes;

    protected $fillable = [
        'name',
        'hierarchy_level',
        'description',
        'is_active',
    ];

    protected $casts = [
        'hierarchy_level' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
