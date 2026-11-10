<?php

namespace App\Filament\Resources\AssetLoans\Pages;

use App\Filament\Resources\AssetLoans\AssetLoanResource;
use Filament\Resources\Pages\ListRecords;

class ListAssetLoans extends ListRecords
{
    protected static string $resource = AssetLoanResource::class;
}
