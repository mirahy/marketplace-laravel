<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(CategorySeeder::class);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $users = User::factory(10)->create();
        $categoryIds = Category::query()->pluck('id');

        Listing::factory(40)
            ->create([
                'user_id' => fn () => $users->random()->id,
                'category_id' => fn () => $categoryIds->random(),
            ])
            ->each(function (Listing $listing) {
                ListingImage::factory(rand(2, 5))
                    ->for($listing)
                    ->sequence(fn ($sequence) => ['order' => $sequence->index])
                    ->create();
            });
    }
}
