<?php

namespace App\Filament\Resources\Bulletins\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
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
                RichEditor::make('content')
                    ->label('Isi Pengumuman')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('image_url')
                    ->label('Poster / Gambar (Opsional)')
                    ->image()
                    ->disk('public')
                    ->directory('bulletins')
                    ->maxSize(5120)
                    ->columnSpanFull(),
                FileUpload::make('document_url')
                    ->label('Lampiran Dokumen PDF (Opsional)')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('public')
                    ->directory('bulletins_docs')
                    ->maxSize(10240)
                    ->columnSpanFull(),
                Select::make('created_by')
                    ->label('Penulis (User)')
                    ->relationship('author', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('expired_at')
                    ->label('Batas Waktu Tampil (Expired Date)')
                    ->helperText('Kosongkan jika pengumuman ini berlaku selamanya tanpa batas waktu.'),
                Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
