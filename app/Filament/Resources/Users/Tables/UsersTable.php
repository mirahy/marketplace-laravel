<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\UserResource;
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
            ->poll('10s')
            ->recordUrl(fn ($record) => UserResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nome'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('E-mail'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label(__('Telefone')),
                TextColumn::make('city')
                    ->label(__('Cidade'))
                    ->formatStateUsing(fn ($state, $record) => trim("{$record->city}/{$record->state}", '/')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                IconColumn::make('is_admin')
                    ->label(__('Admin'))
                    ->boolean(),
                TextColumn::make('roles.name')
                    ->label(__('Papel'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'anunciante' => __('Anunciante'),
                        'usuario' => __('Usuário'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => $state === 'anunciante' ? 'success' : 'gray'),
                TextColumn::make('listings_count')
                    ->counts('listings')
                    ->label(__('Anúncios')),
                TextColumn::make('created_at')
                    ->label(__('Cadastrado em'))
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(UserStatus::class),
                TernaryFilter::make('is_admin')
                    ->label(__('Administrador')),
                SelectFilter::make('roles')
                    ->label(__('Papel'))
                    ->relationship('roles', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('Aprovar'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === UserStatus::Pendente)
                    ->action(fn ($record) => $record->approve()),
                Action::make('reject')
                    ->label(__('Rejeitar'))
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
