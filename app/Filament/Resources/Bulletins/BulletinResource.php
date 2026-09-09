<?php

namespace App\Filament\Resources\Bulletins;

use App\Domains\Portal\Models\Bulletin;
use App\Filament\Resources\Bulletins\Pages\CreateBulletin;
use App\Filament\Resources\Bulletins\Pages\EditBulletin;
use App\Filament\Resources\Bulletins\Pages\ListBulletins;
use App\Filament\Resources\Bulletins\Schemas\BulletinForm;
use App\Filament\Resources\Bulletins\Tables\BulletinsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class BulletinResource extends Resource
{
    protected static ?string $model = Bulletin::class;

    protected static ?string $modelLabel = 'Pengumuman / Mading';

    protected static ?string $pluralModelLabel = 'Daftar Pengumuman';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Pusat Informasi';

    public static function form(Schema $schema): Schema
    {
        return BulletinForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BulletinsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBulletins::route('/'),
            'create' => CreateBulletin::route('/create'),
            'edit' => EditBulletin::route('/{record}/edit'),
        ];
    }
}
