<?php

namespace App\Filament\Resources\Listings\RelationManagers;

use App\Models\ListingImage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('path')
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                ImageColumn::make('path')
                    ->label(__('Foto'))
                    ->disk('public')
                    ->action(
                        Action::make('viewImage')
                            ->label(__('Visualizar'))
                            ->modalHeading(__('Fotos do anúncio'))
                            ->modalSubmitAction(false)
                            ->modalCancelAction(false)
                            ->modalWidth(Width::TwoExtraLarge)
                            ->modalContent(fn (ListingImage $record) => view('filament.listings.images-lightbox', [
                                'images' => $record->listing->images,
                                'initial' => $record->listing->images->search(
                                    fn (ListingImage $image) => $image->is($record)
                                ) ?: 0,
                            ]))
                    ),
            ])
            ->headerActions([])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
