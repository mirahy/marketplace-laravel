<?php

namespace App\Filament\Resources\Conversations\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->defaultSort('created_at')
            ->columns([
                TextColumn::make('sender.name')
                    ->label(__('De')),
                TextColumn::make('body')
                    ->label(__('Mensagem'))
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label(__('Enviada em'))
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
