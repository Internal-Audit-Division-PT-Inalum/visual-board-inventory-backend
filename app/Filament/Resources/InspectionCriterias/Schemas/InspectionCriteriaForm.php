<?php

namespace App\Filament\Resources\InspectionCriterias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class InspectionCriteriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('zone_id')
                    ->label('Zona')
                    ->relationship('zone', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('item_group')
                    ->label('Kelompok Item (Item Group)')
                    ->required(),
                TextInput::make('criteria_code')
                    ->label('Kode Kriteria')
                    ->required(),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->required(),
            ]);
    }
}
