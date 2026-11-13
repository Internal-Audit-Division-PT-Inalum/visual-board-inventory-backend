<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domains\Core\Models\User;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $emailVerifiedAt = Arr::pull($data, 'email_verified_at');

        /** @var User $record */
        $record = static::getModel()::create($data);

        if ($emailVerifiedAt !== null) {
            $record->forceFill(['email_verified_at' => $emailVerifiedAt])->save();
        }

        return $record;
    }
}
