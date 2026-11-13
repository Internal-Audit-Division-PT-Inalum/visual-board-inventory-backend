<?php

namespace App\Domains\Portal\Models;

use App\Domains\Core\Models\User;
use App\Shared\Concerns\HasUlid;
use Database\Factories\BulletinFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bulletin extends Model
{
    /** @use HasFactory<BulletinFactory> */
    use HasFactory;

    use HasUlid, SoftDeletes;

    protected $fillable = [
        'title',
        'content',
        'type',
        'is_active',
        'published_at',
        'created_by',
        'image_url',
        'document_url',
        'expired_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
