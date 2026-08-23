<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class UserApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_user_cannot_log_in(): void
    {
        $user = User::factory()->pending()->create();

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasErrors('form.email');

        $this->assertGuest();
    }

    public function test_rejected_user_cannot_log_in(): void
    {
        $user = User::factory()->create(['status' => UserStatus::Rejeitado]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasErrors('form.email');

        $this->assertGuest();
    }

    public function test_user_can_log_in_after_admin_approval(): void
    {
        $user = User::factory()->pending()->create();

        $user->update(['status' => UserStatus::Aprovado]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_approved_admin_can_still_log_in(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $admin->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component->assertHasNoErrors();

        $this->assertAuthenticatedAs($admin);
    }
}
