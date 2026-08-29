<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ListingCondition: string implements HasLabel
{
    case Novo = 'novo';
    case Usado = 'usado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Novo => __('Novo'),
            self::Usado => __('Usado'),
        };
    }
}
