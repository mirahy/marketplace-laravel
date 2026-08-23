<?php

namespace Tests\Feature\Auth;

use App\Enums\UserStatus;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_register_as_pending_and_are_not_logged_in(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['is_admin' => true]);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('login', absolute: false));

        $this->assertGuest();

        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $this->assertSame(UserStatus::Pendente, $user->status);

        Notification::assertSentTo($admin, NewUserRegistered::class);
    }
}
