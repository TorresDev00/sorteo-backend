<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ParticiparEnSorteoRequest;
use App\Models\Sorteo;
use App\Services\SorteoService;
use Illuminate\Http\JsonResponse;

class SorteoController extends Controller
{
    public function __construct(private readonly SorteoService $sorteoService) {}

    public function info(Sorteo $sorteo): JsonResponse
    {
        if (! $sorteo->activo) {
            abort(422, 'El sorteo no está activo.');
        }

        $semana             = $this->sorteoService->obtenerSemanaActual($sorteo);
        $liberacionSemanal  = $this->sorteoService->obtenerLiberacionActual($sorteo, $semana);
        $premiosDisponibles = $liberacionSemanal?->totalDisponibleReal() ?? 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                  => $sorteo->id,
                'nombre'              => $sorteo->nombre,
                'fecha_inicio'        => $sorteo->fecha_inicio->toDateString(),
                'fecha_fin'           => $sorteo->fecha_fin->toDateString(),
                'semana_actual'       => $semana,
                'premios_disponibles' => $premiosDisponibles,
            ],
            'message' => null,
            'errors'  => null,
        ]);
    }

    public function participar(ParticiparEnSorteoRequest $request, Sorteo $sorteo): JsonResponse
    {
        $fueraDeplazo = now()->toDateString() > $sorteo->fecha_fin->toDateString();

        if (! $sorteo->activo || $fueraDeplazo) {
            abort(422, 'El sorteo no está activo o ha finalizado.');
        }

        $semana = $this->sorteoService->obtenerSemanaActual($sorteo);

        $yaGano = $sorteo->registros()
            ->where('cedula', $request->cedula)
            ->where('semana', $semana)
            ->whereIn('estado', ['preseleccionado', 'verificado'])
            ->exists();

        if ($yaGano) {
            return response()->json([
                'success' => false,
                'data'    => null,
                'message' => 'Ya resultaste ganador esta semana. Tu premio está siendo validado.',
                'errors'  => null,
            ], 409);
        }

        $pathFactura = $request->file('factura_imagen')->store('facturas', 'public');

        $registro = $this->sorteoService->procesarParticipacion(
            $sorteo,
            $request->validated(),
            $pathFactura
        );

        if ($registro->estado === 'preseleccionado') {
            $data = [
                'registro_id' => $registro->id,
                'cedula'      => $registro->cedula,
                'estado'      => $registro->estado,
                'ganador'     => true,
                'premio'      => $registro->premio?->nombre,
                'mensaje'     => '¡FELICIDADES! Has sido preseleccionado. Validaremos tu factura en 48 horas.',
            ];
        } else {
            $data = [
                'registro_id' => $registro->id,
                'cedula'      => $registro->cedula,
                'estado'      => $registro->estado,
                'ganador'     => false,
                'premio'      => null,
                'mensaje'     => 'Gracias por participar. No resultaste ganador esta vez.',
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => null,
            'errors'  => null,
        ], 201);
    }

    public function consultarParticipante(Sorteo $sorteo, string $cedula): JsonResponse
    {
        $registro = $sorteo->registros()
            ->with('premio')
            ->where('cedula', $cedula)
            ->orderByDesc('created_at')
            ->first();

        if (! $registro) {
            abort(404, 'No se encontró participación para esa cédula en este sorteo.');
        }

        [$mensaje, $nombrePremio] = match ($registro->estado) {
            'preseleccionado' => ['Validando tu factura...', $registro->premio?->nombre],
            'verificado'      => ['Premio confirmado ✅', $registro->premio?->nombre],
            'pendiente'       => ['No ganaste esta vez. ¡Sigue participando!', null],
            'rechazado'       => ['Tu factura no pudo ser validada.', null],
            default           => [$registro->estado, null],
        };

        return response()->json([
            'success' => true,
            'data'    => [
                'registro_id' => $registro->id,
                'cedula'      => $registro->cedula,
                'estado'      => $registro->estado,
                'ganador'     => $registro->ganador,
                'premio'      => $nombrePremio,
                'mensaje'     => $mensaje,
            ],
            'message' => null,
            'errors'  => null,
        ]);
    }
}
