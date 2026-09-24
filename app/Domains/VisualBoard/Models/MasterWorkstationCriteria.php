<?php

namespace App\Domains\VisualBoard\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class MasterWorkstationCriteria extends Model
{
    use HasUlids;

    protected $fillable = [
        'item_group',
        'criteria_code',
        'standard_criteria',
        'is_active',
    ];
}
