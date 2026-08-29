<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Enums\CategoryStatus;
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

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nome'))
                    ->searchable(),
                TextColumn::make('icon')
                    ->label(__('Ícone')),
                IconColumn::make('is_active')
                    ->label(__('Ativa'))
                    ->boolean(),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('creator.name')
                    ->label(__('Solicitada por'))
                    ->placeholder('—'),
                TextColumn::make('listings_count')
                    ->counts('listings')
                    ->label(__('Anúncios')),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('Ativa')),
                SelectFilter::make('status')
                    ->label(__('Status'))
                    ->options(CategoryStatus::class),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('Aprovar'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === CategoryStatus::Pendente)
                    ->action(fn ($record) => $record->approve()),
                Action::make('reject')
                    ->label(__('Rejeitar'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === CategoryStatus::Pendente)
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
