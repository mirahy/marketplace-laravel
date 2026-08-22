<?php

namespace App\Filament\Resources\Listings\Tables;

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.path')
                    ->label('Foto')
                    ->limit(1)
                    ->getStateUsing(fn ($record) => optional($record->images->first())->path),
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('category.name')
                    ->label('Categoria'),
                TextColumn::make('user.name')
                    ->label('Vendedor'),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL'),
                TextColumn::make('condition')
                    ->label('Condição')
                    ->badge(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('city')
                    ->label('Local')
                    ->formatStateUsing(fn ($record) => trim("{$record->city}/{$record->state}", '/')),
                TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('Destaque')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(ListingStatus::class),
                SelectFilter::make('category_id')
                    ->label('Categoria')
                    ->options(fn () => Category::query()->pluck('name', 'id')),
                SelectFilter::make('condition')
                    ->options(ListingCondition::class),
                TernaryFilter::make('is_featured'),
                Filter::make('price_range')
                    ->label('Faixa de preço')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('price_min')->numeric()->label('Preço mín.'),
                        \Filament\Forms\Components\TextInput::make('price_max')->numeric()->label('Preço máx.'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['price_min'] ?? null, fn ($q, $v) => $q->where('price', '>=', $v))
                            ->when($data['price_max'] ?? null, fn ($q, $v) => $q->where('price', '<=', $v));
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === ListingStatus::EmAnalise)
                    ->action(fn ($record) => $record->update(['status' => ListingStatus::Ativo, 'published_at' => now()])),
                Action::make('reject')
                    ->label('Rejeitar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === ListingStatus::EmAnalise)
                    ->action(fn ($record) => $record->update(['status' => ListingStatus::Rejeitado])),
                Action::make('toggleFeatured')
                    ->label(fn ($record) => $record->is_featured ? 'Remover destaque' : 'Destacar')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn ($record) => $record->update(['is_featured' => ! $record->is_featured])),
                EditAction::make(),
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
