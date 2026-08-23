<?php

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Livewire\ListingShow;
use App\Models\Category;
use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RelatedListingsTest extends TestCase
{
    use RefreshDatabase;

    private function listing(Category $category, string $title, int $minutesAgo = 0): Listing
    {
        return Listing::factory()->create([
            'category_id' => $category->id,
            'title' => $title,
            'status' => ListingStatus::Ativo,
            'published_at' => now()->subMinutes($minutesAgo),
        ]);
    }

    public function test_first_two_suggestions_come_from_the_current_listings_category(): void
    {
        $categoryA = Category::factory()->create();
        $current = $this->listing($categoryA, 'Atual', 0);
        $a1 = $this->listing($categoryA, 'A Mais Recente', 1);
        $a2 = $this->listing($categoryA, 'A Segunda Mais Recente', 2);
        $this->listing($categoryA, 'A Terceira', 3);

        Livewire::test(ListingShow::class, ['listing' => $current])
            ->assertSeeInOrder([$a1->title, $a2->title]);
    }

    public function test_remaining_slots_are_filled_from_view_history_categories_in_recency_order(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();
        $categoryC = Category::factory()->create();

        $current = $this->listing($categoryA, 'Atual', 0);
        $a1 = $this->listing($categoryA, 'A1', 1);
        $a2 = $this->listing($categoryA, 'A2', 2);

        // Viewed C before B, so B is the most recently viewed category.
        $viewedC = $this->listing($categoryC, 'Visitado C', 10);
        $viewedB = $this->listing($categoryB, 'Visitado B', 11);

        $b1 = $this->listing($categoryB, 'B1', 3);
        $b2 = $this->listing($categoryB, 'B2', 4);
        $c1 = $this->listing($categoryC, 'C1', 5);
        $c2 = $this->listing($categoryC, 'C2', 6);

        Livewire::test(ListingShow::class, ['listing' => $current])
            ->call('setViewHistory', [$viewedB->id, $viewedC->id])
            ->assertSeeInOrder([
                $a1->title, $a2->title, // category slot
                $b1->title, $b2->title, // most recently viewed category first
                $c1->title, $c2->title,
            ])
            ->assertDontSee($viewedB->title)
            ->assertDontSee($viewedC->title);
    }

    public function test_all_ten_suggestions_come_from_history_when_current_category_has_no_other_items(): void
    {
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $current = $this->listing($categoryA, 'Atual Sozinho', 0);
        $viewedB = $this->listing($categoryB, 'Visitado B', 10);

        $extras = collect(range(1, 11))->map(
            fn ($i) => $this->listing($categoryB, "B Extra {$i}", $i)
        );

        $component = Livewire::test(ListingShow::class, ['listing' => $current])
            ->call('setViewHistory', [$viewedB->id]);

        foreach ($extras->take(10) as $expected) {
            $component->assertSee($expected->title);
        }

        $component->assertDontSee($extras->last()->title);
    }

    public function test_suggestions_never_exceed_ten_and_exclude_current_and_history_listings(): void
    {
        $categoryA = Category::factory()->create();
        $current = $this->listing($categoryA, 'Atual', 0);
        $viewed = $this->listing($categoryA, 'Ja Visto', 20);

        collect(range(1, 15))->each(
            fn ($i) => $this->listing($categoryA, "Sugestao {$i}", $i)
        );

        $related = Livewire::test(ListingShow::class, ['listing' => $current])
            ->call('setViewHistory', [$viewed->id])
            ->instance()
            ->render()
            ->getData()['related'];

        $this->assertLessThanOrEqual(10, $related->count());
        $this->assertFalse($related->contains('id', $current->id));
        $this->assertFalse($related->contains('id', $viewed->id));
    }

    public function test_guest_visiting_the_page_directly_sees_category_based_suggestions(): void
    {
        $category = Category::factory()->create();
        $current = Listing::factory()->create(['category_id' => $category->id, 'status' => ListingStatus::Ativo]);
        $sibling = $this->listing($category, 'Irmão de Categoria', 1);

        $this->get('/anuncios/'.$current->slug)
            ->assertOk()
            ->assertSee($sibling->title);
    }
}
