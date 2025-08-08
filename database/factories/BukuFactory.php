<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Buku>
 */
class BukuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul_buku' => fake()->sentence($nbWords = rand(2,6), $variableNbWords = true),
            'penerbit' => fake()->company(),
            'dimensi' => fake()->randomElement([
                '14.5 x 21',
                '13 x 19',
                '13.5 x 20',
                '14 x 21',
            ]),
            'stock' => rand(10, 30)
        ];
    }
}
