<?php

namespace App\Exports;

use App\Models\Registro;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RegistrosExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(protected Builder $query) {}

    public function query(): Builder
    {
        return $this->query->with('premio');
    }

    public function title(): string
    {
        return 'Participantes';
    }

    public function headings(): array
    {
        return [
            'Cédula',
            'Nombre',
            'Teléfono',
            'Dirección',
            'Lugar de compra',
            'Semana',
            'Estado',
            '¿Ganó?',
            'Premio ganado',
            'Fecha de registro',
        ];
    }

    public function map($row): array
    {
        static $estados = [
            'pendiente'       => 'Pendiente',
            'preseleccionado' => 'Preseleccionado',
            'verificado'      => 'Verificado',
            'rechazado'       => 'Rechazado',
        ];

        return [
            $row->cedula,
            $row->nombre,
            $row->telefono,
            $row->direccion,
            $row->lugar_compra,
            $row->semana,
            $estados[$row->estado] ?? $row->estado,
            $row->ganador ? 'Sí' : 'No',
            $row->premio?->nombre ?? '—',
            $row->fecha_registro?->format('d/m/Y H:i') ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $ws = $event->sheet->getDelegate();
                $lastCol = 'J';

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
