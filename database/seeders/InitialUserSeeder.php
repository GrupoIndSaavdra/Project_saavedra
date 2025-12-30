<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['matricula' => '44444'],
            [
                'nombre' => 'Master',
                'a_paterno' => 'GIS',
                'a_materno' => 'Saavedra',
                'contrasena' => bcrypt('GIS20250811'),
                'perfil' => 3,
            ]
        );
    }
}
