<?php

namespace App\Filament\Resources\Conversations\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('listing.title')
                    ->label('Anúncio')
                    ->searchable(),
                TextColumn::make('buyer.name')
                    ->label('Comprador'),
                TextColumn::make('seller.name')
                    ->label('Vendedor'),
                TextColumn::make('messages_count')
                    ->counts('messages')
                    ->label('Mensagens'),
                TextColumn::make('updated_at')
                    ->label('Última atividade')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
