<?php

namespace App\Filament\Resources\MasterWorkstationCriterias\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterWorkstationCriteriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()->schema([
                    Select::make('item_group')
                        ->label('Area/Grup')
                        ->options([
                            'Meja Kerja' => 'Meja Kerja',
                            'Laci' => 'Laci',
                            'Kabel & Peralatan' => 'Kabel & Peralatan',
                            'Papan Informasi' => 'Papan Informasi',
                        ])
                        ->required(),
                    TextInput::make('criteria_code')
                        ->label('Kode Kriteria')
                        ->placeholder('Contoh: R-1')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('standard_criteria')
                        ->label('Standar Penilaian')
                        ->required()
                        ->maxLength(65535)
                        ->columnSpanFull(),
                    Toggle::make('is_active')
                        ->label('Status Aktif')
                        ->default(true)
                        ->required(),
                ])->columns(2),
            ]);
    }
}
