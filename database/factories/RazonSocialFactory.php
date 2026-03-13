<?php

namespace Database\Factories;

use App\Models\Proyecto;
use Illuminate\Database\Eloquent\Factories\Factory;

class RazonSocialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_proyecto' => Proyecto::inRandomOrder()->first()->id_proyecto ?? Proyecto::factory(),
            'nombre_razon_social' => fake()->company(),
            'rfc' => fake()->unique()->bothify('????#########'),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'direccion' => fake()->address(),
        ];
    }
}
