<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('Quando'))
                            ->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('causer.name')
                            ->label(__('Realizado por'))
                            ->placeholder(__('Sistema')),
                        TextEntry::make('event')
                            ->label(__('Evento'))
                            ->badge()
                            ->formatStateUsing(fn (?string $state) => ActivityLogResource::eventLabel($state))
                            ->color(fn (?string $state) => ActivityLogResource::eventColor($state)),
                        TextEntry::make('subject_type')
                            ->label(__('Tipo'))
                            ->formatStateUsing(fn (?string $state) => ActivityLogResource::subjectTypeLabel($state)),
                        TextEntry::make('description')
                            ->label(__('Descrição'))
                            ->columnSpanFull(),
                    ]),
                Section::make()
                    ->columns(2)
                    ->schema([
                        KeyValueEntry::make('properties.old')
                            ->label(__('Valores antigos'))
                            ->visible(fn ($record) => filled($record->properties?->get('old'))),
                        KeyValueEntry::make('properties.attributes')
                            ->label(__('Valores novos'))
                            ->visible(fn ($record) => filled($record->properties?->get('attributes'))),
                    ]),
            ]);
    }
}
