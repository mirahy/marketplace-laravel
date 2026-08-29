<?php

namespace App\Filament\Resources\Conversations\Tables;

use App\Filament\Resources\Conversations\ConversationResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->poll('10s')
            ->recordUrl(fn ($record) => ConversationResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('listing.title')
                    ->label(__('Anúncio'))
                    ->searchable(),
                TextColumn::make('buyer.name')
                    ->label(__('Comprador')),
                TextColumn::make('seller.name')
                    ->label(__('Vendedor')),
                TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label(__('Mensagens')),
                TextColumn::make('updated_at')
                    ->label(__('Última atividade'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
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
