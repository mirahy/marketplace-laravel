<?php

namespace Database\Factories;

use App\Enums\ListingCondition;
use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    public function definition(): array
    {
        $title = Str::title(fake()->words(3, true));

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->randomFloat(2, 20, 5000),
            'condition' => fake()->randomElement(ListingCondition::cases())->value,
            'status' => ListingStatus::Ativo->value,
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'views_count' => fake()->numberBetween(0, 500),
            'is_featured' => fake()->boolean(20),
            'published_at' => now(),
        ];
    }
}
