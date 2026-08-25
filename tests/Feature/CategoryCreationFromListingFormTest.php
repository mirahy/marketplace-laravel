<?php

namespace Tests\Feature;

use App\Enums\CategoryStatus;
use App\Livewire\ListingForm;
use App\Models\Category;
use App\Models\User;
use App\Notifications\NewCategoryRequested;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryCreationFromListingFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_a_new_category_that_starts_pending_and_gets_selected(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->anunciante()->create();

        Livewire::actingAs($user)
            ->test(ListingForm::class)
            ->set('newCategoryName', 'Instrumentos Musicais')
            ->call('createCategory');

        $category = Category::query()->where('name', 'Instrumentos Musicais')->firstOrFail();

        $this->assertSame(CategoryStatus::Pendente, $category->status);
        $this->assertFalse($category->is_active);
        $this->assertSame($user->id, $category->created_by);

        Notification::assertSentTo($admin, NewCategoryRequested::class);
    }

    public function test_pending_category_is_hidden_from_public_pages_but_visible_to_its_author_in_the_form(): void
    {
        $author = User::factory()->anunciante()->create();
        $otherUser = User::factory()->anunciante()->create();
        $category = Category::factory()->create([
            'name' => 'Ferramentas Raras',
            'is_active' => false,
            'status' => CategoryStatus::Pendente,
            'created_by' => $author->id,
        ]);

        $this->get('/')->assertOk()->assertDontSee('Ferramentas Raras');
        $this->get('/anuncios')->assertOk()->assertDontSee('Ferramentas Raras');

        Livewire::actingAs($otherUser)
            ->test(ListingForm::class)
            ->assertDontSee('Ferramentas Raras');

        Livewire::actingAs($author)
            ->test(ListingForm::class)
            ->set('categoryId', $category->id)
            ->assertSee('Ferramentas Raras');
    }

    public function test_pending_category_page_returns_404(): void
    {
        $category = Category::factory()->create([
            'is_active' => false,
            'status' => CategoryStatus::Pendente,
        ]);

        $this->get('/categorias/'.$category->slug)->assertNotFound();
    }
}
