<?php

namespace App\Filament\Resources\Bulletins\QuickLinks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuickLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul / Nama Sistem')
                    ->required()
                    ->maxLength(255),
                TextInput::make('url')
                    ->label('Tautan (URL)')
                    ->required()
                    ->url()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('Deskripsi Singkat')
                    ->maxLength(255),
                TextInput::make('icon')
                    ->label('Nama Icon (misal: lucide-link)')
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
