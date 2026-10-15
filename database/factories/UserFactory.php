<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'business_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '3' . fake()->numerify('#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password123'),
            'role' => UserRole::Owner,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function superadmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'business_id' => null,
            'role' => UserRole::Superadmin,
            'status' => UserStatus::Active,
        ]);
    }

    public function owner(?Business $business = null): static
    {
        return $this->state(fn (array $attributes) => [
            'business_id' => $business?->id ?? Business::factory(),
            'role' => UserRole::Owner,
            'status' => UserStatus::Active,
        ]);
    }

    public function driver(?Business $business = null): static
    {
        return $this->state(fn (array $attributes) => [
            'business_id' => $business?->id ?? Business::factory(),
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Suspended,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Inactive,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
