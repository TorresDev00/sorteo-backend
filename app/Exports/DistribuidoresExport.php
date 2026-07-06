<?php

namespace App\Exports;

use App\Models\Distribuidor;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DistribuidoresExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(protected Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function title(): string
    {
        return 'Distribuidores';
    }

    public function headings(): array
    {
        return [
            'Nombre comercial',
            'Email',
            'Teléfono',
            'Estado / Ubicación',
            'Mensaje',
            'Estatus del lead',
            'Notas del administrador',
            'Fecha de solicitud',
        ];
    }

    public function map($row): array
    {
        static $estatuses = [
            'nuevo'      => 'Nuevo',
            'contactado' => 'Contactado',
            'rechazado'  => 'Rechazado',
        ];

        return [
            $row->nombre_comercial,
            $row->email,
            $row->telefono,
            $row->estado_ubicacion,
            $row->mensaje,
            $estatuses[$row->estatus_lead] ?? $row->estatus_lead,
            $row->notas_administrador,
            $row->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $lastCol = 'H';

                $ws->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF00247D'],
                    ],
                ]);

                $ws->freezePane('A2');
                $ws->setAutoFilter("A1:{$lastCol}1");
            },
        ];
    }
}
