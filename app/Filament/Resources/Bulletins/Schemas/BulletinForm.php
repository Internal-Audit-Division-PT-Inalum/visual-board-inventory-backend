<?php

namespace App\Filament\Resources\Bulletins\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BulletinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul Pengumuman')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->label('Kategori')
                    ->options([
                        'general' => 'Umum',
                        'health_safety' => 'K3 / Keselamatan',
                        'event' => 'Acara / Event',
                        'policy' => 'Kebijakan',
                    ])
                    ->required()
                    ->searchable(),
                Textarea::make('content')
                    ->label('Isi Pengumuman')
                    ->required()
                    ->columnSpanFull(),
                Select::make('created_by')
                    ->label('Penulis (User)')
                    ->relationship('author', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
