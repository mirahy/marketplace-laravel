<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Concerns\HasFullWidthForm;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    use HasFullWidthForm;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles([$this->data['role']]);
    }
}
