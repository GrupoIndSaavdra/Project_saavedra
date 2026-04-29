<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MetasFactory extends Factory
{
    protected $model = \App\Models\Metas::class;

    public function definition(): array
    {
        return [
            'id_ot' => 'OT-TEST',
            'id_usuario' => '12345',
            'fecha' => now()->format('Y-m-d'),
            'h_inicio' => '08:00:00',
            'h_termino' => '10:00:00',
            'maquina' => '1',
            'id_clase' => 1,
            'proceso' => 'Cepillado',
        ];
    }
}
