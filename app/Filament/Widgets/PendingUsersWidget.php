<?php

namespace App\Filament\Widgets;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingUsersWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Usuários pendentes'))
            ->query(User::query()->where('status', UserStatus::Pendente)->latest())
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nome')),
                TextColumn::make('email')
                    ->label(__('E-mail')),
                TextColumn::make('phone')
                    ->label(__('Telefone')),
                TextColumn::make('created_at')
                    ->label(__('Cadastrado em'))
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('Aprovar'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn ($record) => $record->approve()),
                Action::make('reject')
                    ->label(__('Rejeitar'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn ($record) => $record->reject()),
            ]);
    }
}
