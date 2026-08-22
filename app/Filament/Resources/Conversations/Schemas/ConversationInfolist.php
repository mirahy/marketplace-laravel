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
                    ->label('Anúncio'),
                TextEntry::make('buyer.name')
                    ->label('Comprador'),
                TextEntry::make('seller.name')
                    ->label('Vendedor'),
                TextEntry::make('created_at')
                    ->label('Iniciada em')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
