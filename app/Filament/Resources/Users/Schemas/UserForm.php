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
                            ->label(__('Nome'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('E-mail'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label(__('Senha'))
                            ->password()
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->required(fn (string $operation) => $operation === 'create')
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('Telefone'))
                            ->tel()
                            ->mask('(99) 99999-9999')
                            ->maxLength(20),
                        TextInput::make('city')
                            ->label(__('Cidade'))
                            ->maxLength(255),
                        TextInput::make('state')
                            ->label(__('UF'))
                            ->maxLength(2),
                        Select::make('role')
                            ->label(__('Papel'))
                            ->options([
                                'anunciante' => __('Anunciante'),
                                'usuario' => __('Usuário'),
                            ])
                            ->required()
                            ->default('usuario')
                            ->afterStateHydrated(fn (Select $component, $record) => $component->state(
                                $record?->roles->first()?->name ?? 'usuario'
                            ))
                            ->dehydrated(false),
                        Select::make('status')
                            ->label(__('Status'))
                            ->options(UserStatus::class)
                            ->required(),
                        Toggle::make('show_phone')
                            ->label(__('Exibir telefone nos anúncios')),
                        Toggle::make('show_phone_to_guests')
                            ->label(__('Exibir contato para usuários não cadastrados')),
                        Toggle::make('is_admin')
                            ->label(__('Administrador')),
                    ]),
            ]);
    }
}
