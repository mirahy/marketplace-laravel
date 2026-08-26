<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
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
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20),
                        Toggle::make('show_phone')
                            ->label('Exibir telefone nos anúncios'),
                        TextInput::make('city')
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label('UF')
                            ->maxLength(2),
                        Toggle::make('is_admin')
                            ->label('Administrador'),
                        Select::make('role')
                            ->label('Papel')
                            ->options([
                                'anunciante' => 'Anunciante',
                                'usuario' => 'Usuário',
                            ])
                            ->required()
                            ->default('usuario')
                            ->afterStateHydrated(fn (Select $component, $record) => $component->state(
                                $record?->roles->first()?->name ?? 'usuario'
                            ))
                            ->dehydrated(false),
                        Select::make('status')
                            ->label('Status')
                            ->options(UserStatus::class)
                            ->required(),
                    ]),
            ]);
    }
}
