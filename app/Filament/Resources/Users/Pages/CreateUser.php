<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\HasFullWidthForm;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    use HasFullWidthForm;

    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->record->syncRoles([$this->data['role']]);
    }
}
