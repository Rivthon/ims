<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_number' => 'DOC-'.fake()->unique()->numerify('#####'),
            'title' => fake()->sentence(4),
            'category' => fake()->randomElement(['SOP ICT', 'Kebijakan Keamanan', 'Instruksi Kerja']),
            'status' => 'draft',
            'current_version' => '1.0',
            'effective_date' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'created_by' => User::factory(),
        ];
    }
}
