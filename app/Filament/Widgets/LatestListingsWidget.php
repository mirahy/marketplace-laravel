<?php

namespace App\Filament\Widgets;

use App\Models\Listing;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestListingsWidget extends TableWidget
{
    protected static ?string $heading = 'Últimos anúncios criados';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Listing::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->limit(40),
                TextColumn::make('category.name')
                    ->label('Categoria'),
                TextColumn::make('user.name')
                    ->label('Vendedor'),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
