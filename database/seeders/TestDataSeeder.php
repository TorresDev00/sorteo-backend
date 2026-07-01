<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sorteo;
use App\Models\Premio;
use App\Models\LiberacionSemanal;
use App\Models\LiberacionPremio;


class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sorteo = Sorteo::create([
            'nombre'       => 'Sorteo de prueba',
            'fecha_inicio' => now()->subDays(1),
            'fecha_fin'    => now()->addDays(1),
            'activo'       => true,
        ]);

         $premios = [
            ['nombre' => 'Lavadora LG 12kg', 'tipo' => 'electrodomesticos', 'cantidad_total' => 5, 'cantidad_disponible' => 5],
            ['nombre' => 'Refri Samsung 25 pies', 'tipo' => 'electrodomesticos', 'cantidad_total' => 3, 'cantidad_disponible' => 3],
            ['nombre' => 'Camiseta Yaracuy XL', 'tipo' => 'merch', 'cantidad_total' => 10, 'cantidad_disponible' => 10],
        ];

        foreach ($premios as $p) {
            Premio::create([...$p, 'sorteo_id' => $sorteo->id]);
        }

        $lote = LiberacionSemanal::create([
            'sorteo_id' => $sorteo->id,
            'semana' => 1,
            'fecha_liberacion' => now(),
            'notas' => 'Primera semana del sorteo',
        ]);

        foreach ($sorteo->premios as $premio) {
            LiberacionPremio::create([
                'liberacion_semanal_id' => $lote->id,
                'premio_id' => $premio->id,
                'cantidad' => 3,
                'cantidad_entregada' => 0,
                'cantidad_reservada' => 0,
            ]);
        }

    }
}
