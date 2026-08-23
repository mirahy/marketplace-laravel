<?php

namespace App\Support;

class BrazilianCities
{
    /** @var array<string, array<int, string>>|null */
    protected static ?array $data = null;

    /**
     * @return array<int, string>
     */
    public static function forState(?string $uf): array
    {
        if (! $uf) {
            return [];
        }

        static::$data ??= json_decode(
            file_get_contents(resource_path('data/municipios-por-uf.json')),
            true,
        );

        return static::$data[strtoupper($uf)] ?? [];
    }
}
