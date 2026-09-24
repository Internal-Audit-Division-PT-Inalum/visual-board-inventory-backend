<?php

namespace App\Domains\VisualBoard\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FiveREvaluation extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'month',
        'year',
        'type',
        'total_score',
        'evaluation_file',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'total_score' => 'decimal:2',
    ];
}
