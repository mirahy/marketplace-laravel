<?php

namespace App\Filament\Widgets;

use App\Enums\CategoryStatus;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingCategoriesWidget extends TableWidget
{
    protected static ?string $heading = 'Categorias pendentes';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Category::query()->where('status', CategoryStatus::Pendente)->latest())
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Nome'),
                TextColumn::make('creator.name')
                    ->label('Solicitada por')
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label('Criada em')
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
