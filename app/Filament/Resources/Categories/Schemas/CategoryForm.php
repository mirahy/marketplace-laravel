<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CategoryStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nome'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug($state ?? ''))),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('icon')
                            ->label(__('Ícone (heroicon)'))
                            ->placeholder('heroicon-o-device-phone-mobile')
                            ->maxLength(255),
                        TextInput::make('order')
                            ->label(__('Ordem'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('Ativa'))
                            ->default(true),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(CategoryStatus::class)
                            ->default(CategoryStatus::Aprovado)
                            ->required(),
                    ]),
            ]);
    }
}
