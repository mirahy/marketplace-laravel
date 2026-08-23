<?php

namespace Tests\Feature;

use App\Enums\CategoryStatus;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use App\Notifications\CategoryStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_a_pending_category(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $category = Category::factory()->create([
            'is_active' => false,
            'status' => CategoryStatus::Pendente,
            'created_by' => $author->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListCategories::class)
            ->callTableAction('approve', $category);

        $category->refresh();

        $this->assertSame(CategoryStatus::Aprovado, $category->status);
        $this->assertTrue($category->is_active);

        Notification::assertSentTo($author, CategoryStatusUpdated::class);
    }

    public function test_admin_can_reject_a_pending_category(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);
        $author = User::factory()->create();
        $category = Category::factory()->create([
            'is_active' => false,
            'status' => CategoryStatus::Pendente,
            'created_by' => $author->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListCategories::class)
            ->callTableAction('reject', $category);

        $category->refresh();

        $this->assertSame(CategoryStatus::Rejeitado, $category->status);
        $this->assertFalse($category->is_active);

        Notification::assertSentTo($author, CategoryStatusUpdated::class);
    }
}
