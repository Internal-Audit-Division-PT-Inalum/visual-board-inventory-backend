<?php

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

arch('application code does not call debug helpers or env directly')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'env'])
    ->not->toBeUsed();

arch('domain layer does not depend on http or filament presentation layers')
    ->expect('App\Domains')
    ->not->toUse([
        'App\Http',
        'App\Filament',
    ]);

arch('domain exceptions extend base Exception')
    ->expect('App\Domains\*\Exceptions')
    ->classes()
    ->toExtend(Exception::class);

arch('http form requests extend FormRequest')
    ->expect('App\Http\Requests')
    ->classes()
    ->toExtend(FormRequest::class);

arch('api controllers do not invoke DB facade directly')
    ->expect('App\Http\Controllers\Api')
    ->not->toUse(DB::class);
