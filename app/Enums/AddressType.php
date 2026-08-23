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
            self::Rua => 'Rua',
            self::Avenida => 'Avenida',
            self::Alameda => 'Alameda',
            self::Travessa => 'Travessa',
            self::Praca => 'Praça',
            self::Rodovia => 'Rodovia',
            self::Estrada => 'Estrada',
            self::Outro => 'Outro',
        };
    }
}
