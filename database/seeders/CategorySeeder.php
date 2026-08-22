<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Eletrônicos', 'icon' => 'heroicon-o-device-phone-mobile'],
            ['name' => 'Celulares', 'icon' => 'heroicon-o-device-phone-mobile'],
            ['name' => 'Veículos', 'icon' => 'heroicon-o-truck'],
            ['name' => 'Imóveis', 'icon' => 'heroicon-o-home'],
            ['name' => 'Moda e Beleza', 'icon' => 'heroicon-o-sparkles'],
            ['name' => 'Casa e Jardim', 'icon' => 'heroicon-o-home-modern'],
            ['name' => 'Esportes e Lazer', 'icon' => 'heroicon-o-trophy'],
            ['name' => 'Bebês e Crianças', 'icon' => 'heroicon-o-face-smile'],
            ['name' => 'Livros e Hobbies', 'icon' => 'heroicon-o-book-open'],
            ['name' => 'Móveis', 'icon' => 'heroicon-o-squares-2x2'],
        ];

        foreach ($categories as $index => $category) {
            Category::query()->create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'icon' => $category['icon'],
                'order' => $index,
                'is_active' => true,
            ]);
        }
    }
}
