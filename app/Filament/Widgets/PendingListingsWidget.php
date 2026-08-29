<?php

namespace App\Filament\Widgets;

use App\Enums\ListingStatus;
use App\Models\Listing;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingListingsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Anúncios pendentes'))
            ->query(Listing::query()->where('status', ListingStatus::EmAnalise)->latest())
            ->paginated(false)
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
                TextColumn::make('created_at')
                    ->label(__('Criado em'))
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label(__('Aprovar'))
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn ($record) => $record->approve()),
                Action::make('reject')
                    ->label(__('Rejeitar'))
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn ($record) => $record->reject()),
            ]);
    }
}
