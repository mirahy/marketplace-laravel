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
    protected static ?string $heading = 'Anúncios pendentes';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Listing::query()->where('status', ListingStatus::EmAnalise)->latest())
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
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(fn ($record) => $record->approve()),
                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn ($record) => $record->reject()),
            ]);
    }
}
