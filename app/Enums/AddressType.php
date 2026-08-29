<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AddressType: string implements HasLabel
{
    case Rua = 'rua';
    case Avenida = 'avenida';
    case Alameda = 'alameda';
    case Travessa = 'travessa';
    case Praca = 'praca';
    case Rodovia = 'rodovia';
    case Estrada = 'estrada';
    case Outro = 'outro';

    public function getLabel(): string
    {
        return match ($this) {
            self::Rua => __('Rua'),
            self::Avenida => __('Avenida'),
            self::Alameda => __('Alameda'),
            self::Travessa => __('Travessa'),
            self::Praca => __('Praça'),
            self::Rodovia => __('Rodovia'),
            self::Estrada => __('Estrada'),
            self::Outro => __('Outro'),
        };
    }
}
