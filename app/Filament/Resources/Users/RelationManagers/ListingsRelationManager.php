<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Listings\ListingResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListingsRelationManager extends RelationManager
{
    protected static string $relationship = 'listings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('Anúncios');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn ($record) => ListingResource::getUrl('edit', ['record' => $record]))
            ->columns([
                ImageColumn::make('images.path')
                    ->label(__('Foto'))
                    ->limit(1)
                    ->getStateUsing(fn ($record) => optional($record->images->first())->path),
                TextColumn::make('title')
                    ->label(__('Título'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('Categoria')),
                TextColumn::make('price')
                    ->label(__('Preço'))
                    ->money('BRL'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Criado em'))
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
