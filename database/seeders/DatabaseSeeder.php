<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Esto creará el usuario administrador automáticamente al usar seeders
        User::updateOrCreate(
            ['email' => 'ejtr18@gmail.com'],
            [
                'name' => 'Admin Yaracuy',
                'password' => Hash::make('123123123'), // Cambia esto por tu clave
            ]
        );
    }
}
