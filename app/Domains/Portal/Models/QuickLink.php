<?php

namespace App\Domains\Portal\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class QuickLink extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'description',
        'url',
        'icon',
        'is_active',
    ];
}
