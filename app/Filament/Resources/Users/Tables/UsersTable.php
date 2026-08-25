<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone'),
                TextColumn::make('city')
                    ->formatStateUsing(fn ($state, $record) => trim("{$record->city}/{$record->state}", '/')),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                TextColumn::make('roles.name')
                    ->label('Papel')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'anunciante' => 'Anunciante',
                        'usuario' => 'Usuário',
                        default => $state,
                    })
                    ->color(fn (string $state): string => $state === 'anunciante' ? 'success' : 'gray'),
                TextColumn::make('listings_count')
                    ->counts('listings')
                    ->label('Anúncios'),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(UserStatus::class),
                TernaryFilter::make('is_admin'),
                SelectFilter::make('roles')
                    ->label('Papel')
                    ->relationship('roles', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === UserStatus::Pendente)
                    ->action(fn ($record) => $record->approve()),
                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === UserStatus::Pendente)
                    ->action(fn ($record) => $record->reject()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
