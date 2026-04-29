<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClaseFactory extends Factory
{
    protected $model = \App\Models\Clase::class;

    public function definition(): array
    {
        return [
            'id_ot' => fake()->numerify('OT-####'),
            'nombre' => 'BOMBILLO',
            'tamanio' => 'Standard',
            'seccion' => 1,
            'piezas' => 100,
            'pedido' => 100,
            'fecha_inicio' => now()->format('Y-m-d'),
            'hora_inicio' => now()->format('H:i:s'),
            'finalizada' => 0,
        ];
    }
}
