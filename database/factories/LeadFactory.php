<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(['new', 'in_progress', 'closed']),
            'user_id' => User::where('type', 'sales')->inRandomOrder()->first()?->id,
            'branch_id' => Branch::inRandomOrder()->first()?->id,
        ];
    }

    public function new(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'new',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'closed',
        ]);
    }
}
