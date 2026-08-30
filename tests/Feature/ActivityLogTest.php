<?php

namespace Tests\Feature;

use App\Enums\CategoryStatus;
use App\Enums\ListingStatus;
use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_category_logs_an_activity_with_the_causer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        $category = Category::factory()->create(['name' => 'Eletrodomésticos']);

        $activity = Activity::query()->forSubject($category)->forEvent('created')->first();

        $this->assertNotNull($activity);
        $this->assertTrue($activity->causer->is($admin));
        $this->assertSame('Eletrodomésticos', $activity->properties->get('attributes')['name']);
    }

    public function test_updating_a_listing_logs_only_the_dirty_attributes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $listing = Listing::factory()->create(['status' => ListingStatus::EmAnalise]);

        $this->actingAs($admin);
        $listing->approve();

        $activity = Activity::query()->forSubject($listing)->forEvent('updated')->latest('id')->first();

        $this->assertNotNull($activity);
        $this->assertArrayHasKey('status', $activity->properties->get('attributes'));
        $this->assertArrayNotHasKey('title', $activity->properties->get('attributes'));
    }

    public function test_soft_deleting_a_user_logs_a_deleted_activity(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin);
        $user->delete();

        $activity = Activity::query()->forSubject($user)->forEvent('deleted')->first();

        $this->assertNotNull($activity);
    }

    public function test_password_changes_are_never_logged(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($admin);
        $user->update(['password' => bcrypt('nova-senha-secreta')]);

        $activity = Activity::query()->forSubject($user)->forEvent('updated')->latest('id')->first();

        // As password is the only attribute changed and it's excluded from
        // logging, no activity should be recorded at all for this update.
        $this->assertNull($activity);

        $user->update(['password' => bcrypt('outra-senha'), 'city' => 'Navirai']);

        $activity = Activity::query()->forSubject($user)->forEvent('updated')->latest('id')->first();

        $this->assertNotNull($activity);
        $this->assertArrayNotHasKey('password', $activity->properties->get('attributes') ?? []);
        $this->assertArrayHasKey('city', $activity->properties->get('attributes') ?? []);
    }

    public function test_admin_can_see_the_activity_log_list_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);
        Category::factory()->create(['name' => 'Eletrodomésticos']);

        $response = $this->get('/admin/activity-logs');

        $response->assertOk()->assertSee(__('Categoria'))->assertSee(__('Criado'));
    }

    public function test_activity_log_resource_is_read_only(): void
    {
        $this->assertFalse(\App\Filament\Resources\ActivityLogs\ActivityLogResource::canCreate());

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::factory()->create(['status' => CategoryStatus::Pendente]);
        $this->actingAs($admin);

        $activity = Activity::query()->forSubject($category)->forEvent('created')->first();

        $this->assertFalse(\App\Filament\Resources\ActivityLogs\ActivityLogResource::canEdit($activity));
    }
}
