<?php

namespace App\Filament\Resources\Listings\Pages;

use App\Filament\Resources\Concerns\HasFullWidthForm;
use App\Filament\Resources\Listings\ListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateListing extends CreateRecord
{
    use HasFullWidthForm;

    protected static string $resource = ListingResource::class;
}
