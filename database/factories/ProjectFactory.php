<?php

namespace Database\Factories;

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-2 months', '+1 month');
        $dueDate = fake()->dateTimeBetween($startDate, '+4 months');

        return [
            'client_name' => fake()->company(),
            'project_name' => fake()->catchPhrase(),
            'description' => fake()->sentence(12),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'priority' => fake()->randomElement(ProjectPriority::cases()),
            'start_date' => $startDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
        ];
    }
}
