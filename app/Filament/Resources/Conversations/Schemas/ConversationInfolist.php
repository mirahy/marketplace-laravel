<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConversationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('listing.title')
                    ->label(__('Anúncio')),
                TextEntry::make('buyer.name')
                    ->label(__('Comprador')),
                TextEntry::make('seller.name')
                    ->label(__('Vendedor')),
                TextEntry::make('created_at')
                    ->label(__('Iniciada em'))
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
