<?php

namespace App\Filament\Resources\Listings\Schemas;

use App\Enums\AddressType;
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
                            ->label(__('Título'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Select::make('user_id')
                            ->label(__('Vendedor'))
                            ->relationship('user', 'name')
                            ->options(fn () => User::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Select::make('category_id')
                            ->label(__('Categoria'))
                            ->relationship('category', 'name')
                            ->options(fn () => Category::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Textarea::make('description')
                            ->label(__('Descrição'))
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('price')
                            ->label(__('Preço'))
                            ->numeric()
                            ->prefix('R$')
                            ->required(),
                        Select::make('condition')
                            ->label(__('Condição'))
                            ->options(ListingCondition::class)
                            ->required(),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(ListingStatus::class)
                            ->required(),
                        TextInput::make('city')
                            ->label(__('Cidade'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label(__('UF'))
                            ->required()
                            ->maxLength(2),
                        Select::make('address_type')
                            ->label(__('Tipo de logradouro'))
                            ->options(AddressType::class),
                        TextInput::make('address_street')
                            ->label(__('Logradouro'))
                            ->maxLength(255),
                        TextInput::make('address_number')
                            ->label(__('Número'))
                            ->maxLength(20),
                        TextInput::make('address_neighborhood')
                            ->label(__('Bairro'))
                            ->maxLength(255),
                        TextInput::make('address_complement')
                            ->label(__('Complemento'))
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->label(__('Destaque')),
                    ]),
            ]);
    }
}
