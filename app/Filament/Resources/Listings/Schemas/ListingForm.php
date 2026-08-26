<?php

namespace App\Filament\Resources\Listings\Schemas;

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label('Vendedor')
                            ->relationship('user', 'name')
                            ->options(fn () => User::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->options(fn () => Category::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label('Preço')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),
                        Select::make('condition')
                            ->label('Condição')
                            ->options(ListingCondition::class)
                            ->required(),
                        Select::make('status')
                            ->label('Status')
                            ->options(ListingStatus::class)
                            ->required(),
                        TextInput::make('city')
                            ->label('Cidade')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label('UF')
                            ->required()
                            ->maxLength(2),
                        Toggle::make('is_featured')
                            ->label('Destaque'),
                    ]),
            ]);
    }
}
