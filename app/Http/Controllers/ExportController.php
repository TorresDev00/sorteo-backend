<?php

namespace App\Http\Controllers;

use App\Exports\DistribuidoresExport;
use App\Exports\RegistrosExport;
use App\Models\Distribuidor;
use App\Models\Registro;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function registros(Request $request): BinaryFileResponse
    {
        $query = Registro::query();

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('semana')) {
            $query->where('semana', $request->semana);
        }

        $filename = 'participantes_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new RegistrosExport($query), $filename);
    }

    public function distribuidores(Request $request): BinaryFileResponse
    {
        $query = Distribuidor::query();

        if ($request->filled('estatus_lead')) {
            $query->where('estatus_lead', $request->estatus_lead);
        }

        $filename = 'distribuidores_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new DistribuidoresExport($query), $filename);
    }
}
