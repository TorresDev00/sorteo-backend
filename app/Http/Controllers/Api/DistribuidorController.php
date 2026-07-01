<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribuidor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DistribuidorController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'nombre_comercial' => ['required', 'string', 'max:255'],
                'email'            => ['required', 'email', Rule::unique('distribuidores', 'email')],
                'telefono'         => ['required', 'string', 'max:50'],
                'estado_ubicacion' => ['required', 'string'],
                'mensaje'          => ['nullable', 'string'],
            ],
            [
                'nombre_comercial.required' => 'El nombre comercial es obligatorio.',
                'nombre_comercial.max'      => 'El nombre comercial no puede superar los 255 caracteres.',
                'email.required'            => 'El correo electrónico es obligatorio.',
                'email.email'               => 'Debe ingresar un correo electrónico válido.',
                'email.unique'              => 'Este correo ya se encuentra registrado.',
                'telefono.required'         => 'El teléfono es obligatorio.',
                'telefono.max'              => 'El teléfono no puede superar los 50 caracteres.',
                'estado_ubicacion.required' => 'El estado/ubicación es obligatorio.',
            ]
        );

        $distribuidor = Distribuidor::create([
            ...$validated,
            'estatus_lead' => 'nuevo',
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $distribuidor->id,
                'nombre_comercial' => $distribuidor->nombre_comercial,
                'estatus_lead'     => $distribuidor->estatus_lead,
                'mensaje'          => 'Recibimos tu solicitud. Te contactaremos pronto.',
            ],
            'message' => null,
            'errors'  => null,
        ], 201);
    }
}
