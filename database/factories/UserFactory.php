<?php

namespace Database\Factories;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'phone' => fake()->numerify('(##) 9####-####'),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'status' => UserStatus::Aprovado,
            'show_phone' => true,
        ];
    }

    /**
     * Assign the default "usuario" role after creation, unless a role has
     * already been assigned (e.g. via the anunciante() state below).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if (! $user->roles()->exists()) {
                $user->assignRole('usuario');
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is still pending admin approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Pendente,
        ]);
    }

    /**
     * Indicate that the user has the "anunciante" role (can create listings).
     */
    public function anunciante(): static
    {
        return $this->afterCreating(fn (User $user) => $user->syncRoles(['anunciante']));
    }
}
