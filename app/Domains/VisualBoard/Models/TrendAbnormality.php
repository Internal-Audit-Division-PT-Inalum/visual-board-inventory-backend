<?php

namespace App\Domains\VisualBoard\Models;

use App\Shared\Concerns\HasUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrendAbnormality extends Model
{
    use HasFactory, HasUlid;

    protected $fillable = [
        'year',
        'month',
        'zone_label',
        'temuan',
        'tindak_lanjut',
        'belum_selesai',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'temuan' => 'integer',
        'tindak_lanjut' => 'integer',
        'belum_selesai' => 'integer',
    ];
}
