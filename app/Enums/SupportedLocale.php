<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SupportedLocale: string implements HasLabel
{
    case PtBr = 'pt_BR';
    case En = 'en';
    case Es = 'es';

    public function getLabel(): string
    {
        return match ($this) {
            self::PtBr => 'Português',
            self::En => 'English',
            self::Es => 'Español',
        };
    }
}
