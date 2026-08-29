<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ListingStatus: string implements HasColor, HasLabel
{
    case EmAnalise = 'em_analise';
    case Ativo = 'ativo';
    case Pausado = 'pausado';
    case Vendido = 'vendido';
    case Rejeitado = 'rejeitado';

    public function getLabel(): string
    {
        return match ($this) {
            self::EmAnalise => __('Em análise'),
            self::Ativo => __('Ativo'),
            self::Pausado => __('Pausado'),
            self::Vendido => __('Vendido'),
            self::Rejeitado => __('Rejeitado'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EmAnalise => 'warning',
            self::Ativo => 'success',
            self::Pausado => 'gray',
            self::Vendido => 'info',
            self::Rejeitado => 'danger',
        };
    }
}
