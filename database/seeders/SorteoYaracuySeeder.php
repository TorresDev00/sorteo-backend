<?php

namespace Database\Seeders;

use App\Models\LiberacionPremio;
use App\Models\LiberacionSemanal;
use App\Models\Premio;
use App\Models\Registro;
use App\Models\Sorteo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SorteoYaracuySeeder extends Seeder
{
    // nombre => [tipo, cantidad_total, [cantidad semana 1..12]]
    private const CATALOGO = [
        'Cafetera digital Oster 12 tazas'   => ['electrodomesticos', 14,  [1,1,1,1,1,1,1,1,2,2,1,1]],
        'Cafetera eléctrica Enduro 12 tazas' => ['electrodomesticos', 15,  [1,1,1,1,1,1,1,1,2,2,2,1]],
        'Batidora de mano Universal Royal'   => ['electrodomesticos', 20,  [2,2,2,2,2,2,2,2,1,1,1,1]],
        'Set de Bowls Enduro'                => ['electrodomesticos', 20,  [3,3,3,3,2,2,2,2,0,0,0,0]],
        'Sandwichera 2 panes'                => ['electrodomesticos', 30,  [2,2,2,2,3,3,4,4,2,2,2,2]],
        'Vaporera Enduro'                    => ['electrodomesticos', 40,  [3,3,3,3,3,3,3,3,4,4,4,4]],
        'Licuadora portátil'                 => ['electrodomesticos', 20,  [1,1,1,1,2,2,2,2,2,2,2,2]],
        'Set de ollas Enduro'                => ['electrodomesticos', 10,  [0,0,0,0,1,1,1,1,1,1,2,2]],
        'Sartén 22 cm'                       => ['electrodomesticos', 30,  [2,2,2,2,2,2,2,2,3,3,4,4]],
        'Televisor 55"'                      => ['electrodomesticos', 1,   [0,0,0,0,0,0,0,0,0,0,0,1]],
        'Gorra Café y Arroz Yaracuy'         => ['merch', 300, [25,25,25,25,25,25,25,25,25,25,25,25]],
        'Taza mug + café 200g'               => ['merch', 20,  [2,2,2,2,2,2,2,2,1,1,1,1]],
        'Cupón café 100g'                    => ['merch', 150, [13,13,13,13,13,13,13,13,11,11,11,13]],
        'Cupón arroz 900g'                   => ['merch', 150, [13,13,13,13,13,13,13,13,11,11,11,13]],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            $sorteo = $this->prepararSorteo();
            $this->limpiarDatosDePrueba($sorteo);
            $premios = $this->crearPremios($sorteo);
            $this->crearLiberacionesSemanales($sorteo, $premios);
        });

        $this->command->info('Catálogo de 12 semanas sembrado correctamente.');
    }

    private function prepararSorteo(): Sorteo
    {
        $sorteo = Sorteo::firstOrCreate(
            ['nombre' => 'Sorteo 2026'],
            ['fecha_inicio' => now()->startOfDay(), 'fecha_fin' => now()->addWeeks(12), 'activo' => true]
        );

        // 12 semanas completas = 84 días. fecha_fin queda en el último día de la semana 12.
        $sorteo->update([
            'fecha_fin' => $sorteo->fecha_inicio->copy()->addDays(83),
            'activo'    => true,
        ]);

        return $sorteo->fresh();
    }

    private function limpiarDatosDePrueba(Sorteo $sorteo): void
    {
        Registro::where('sorteo_id', $sorteo->id)->delete();
        LiberacionSemanal::where('sorteo_id', $sorteo->id)->delete(); // cascada borra liberacion_premios
        Premio::where('sorteo_id', $sorteo->id)->delete();
    }

    private function crearPremios(Sorteo $sorteo): array
    {
        $premios = [];

        foreach (self::CATALOGO as $nombre => [$tipo, $cantidadTotal, $porSemana]) {
            $premios[$nombre] = Premio::create([
                'sorteo_id'           => $sorteo->id,
                'nombre'              => $nombre,
                'tipo'                => $tipo,
                'cantidad_total'      => $cantidadTotal,
                'cantidad_disponible' => $cantidadTotal,
            ]);
        }

        return $premios;
    }

    private function crearLiberacionesSemanales(Sorteo $sorteo, array $premios): void
    {
        for ($semana = 1; $semana <= 12; $semana++) {
            $lote = LiberacionSemanal::create([
                'sorteo_id'        => $sorteo->id,
                'semana'           => $semana,
                'fecha_liberacion' => $sorteo->fecha_inicio->copy()->addWeeks($semana - 1),
                'notas'            => "Semana {$semana} — catálogo Alimentos Yaracuy (12 semanas)",
            ]);

            foreach (self::CATALOGO as $nombre => [$tipo, $cantidadTotal, $porSemana]) {
                $cantidad = $porSemana[$semana - 1];

                if ($cantidad <= 0) {
                    continue; // no se libera ese producto esta semana según la tabla
                }

                LiberacionPremio::create([
                    'liberacion_semanal_id' => $lote->id,
                    'premio_id'             => $premios[$nombre]->id,
                    'cantidad'              => $cantidad,
                    'cantidad_entregada'    => 0,
                    'cantidad_reservada'    => 0,
                ]);
            }
        }
    }
}
