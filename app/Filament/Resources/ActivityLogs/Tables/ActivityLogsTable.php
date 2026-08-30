<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Listing;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('10s')
            ->recordUrl(fn ($record) => ActivityLogResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('Quando'))
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('causer.name')
                    ->label(__('Realizado por'))
                    ->placeholder(__('Sistema')),
                TextColumn::make('event')
                    ->label(__('Evento'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ActivityLogResource::eventLabel($state))
                    ->color(fn (?string $state) => ActivityLogResource::eventColor($state)),
                TextColumn::make('subject_type')
                    ->label(__('Tipo'))
                    ->formatStateUsing(fn (?string $state) => ActivityLogResource::subjectTypeLabel($state)),
                TextColumn::make('description')
                    ->label(__('Descrição'))
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label(__('Evento'))
                    ->options([
                        'created' => __('Criado'),
                        'updated' => __('Atualizado'),
                        'deleted' => __('Excluído'),
                        'restored' => __('Restaurado'),
                    ]),
                SelectFilter::make('subject_type')
                    ->label(__('Tipo'))
                    ->options([
                        User::class => __('Usuário'),
                        Listing::class => __('Anúncio'),
                        Category::class => __('Categoria'),
                        Conversation::class => __('Conversa'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
