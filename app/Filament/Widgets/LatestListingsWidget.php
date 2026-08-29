<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Listings\ListingResource;
use App\Models\Listing;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestListingsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Últimos anúncios criados'))
            ->query(Listing::query()->latest()->limit(5))
            ->paginated(false)
            ->poll('10s')
            ->recordUrl(fn ($record) => ListingResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('title')
                    ->label(__('Título'))
                    ->limit(40),
                TextColumn::make('category.name')
                    ->label(__('Categoria')),
                TextColumn::make('user.name')
                    ->label(__('Vendedor')),
                TextColumn::make('price')
                    ->label(__('Preço'))
                    ->money('BRL'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Criado em'))
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
