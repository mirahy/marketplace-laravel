<?php

namespace App\Filament\Widgets;

use App\Enums\CategoryStatus;
use App\Filament\Resources\Categories\CategoryResource;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingCategoriesWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Categorias pendentes'))
            ->query(Category::query()->where('status', CategoryStatus::Pendente)->latest())
            ->paginated(false)
            ->poll('10s')
            ->recordUrl(fn ($record) => CategoryResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nome')),
                TextColumn::make('creator.name')
                    ->label(__('Solicitada por'))
                    ->placeholder('—'),
                TextColumn::make('created_at')
                    ->label(__('Criada em'))
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
