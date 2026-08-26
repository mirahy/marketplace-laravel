<?php

namespace App\Filament\Resources\Concerns;

use Filament\Support\Enums\Width;

trait HasFullWidthForm
{
    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }
}
