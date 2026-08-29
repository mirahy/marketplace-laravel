<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasColor, HasLabel
{
    case Pendente = 'pendente';
    case Aprovado = 'aprovado';
    case Rejeitado = 'rejeitado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendente => __('Pendente'),
            self::Aprovado => __('Aprovado'),
            self::Rejeitado => __('Rejeitado'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pendente => 'warning',
            self::Aprovado => 'success',
            self::Rejeitado => 'danger',
        };
    }
}
