<?php

namespace App\Filament\Resources\Distribuidors\Pages;

use App\Filament\Resources\Distribuidors\DistribuidorResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDistribuidors extends ListRecords
{
    protected static string $resource = DistribuidorResource::class;

    public function getExportUrl(): string
    {
        $params = [];
        $filters = $this->tableFilters ?? [];

        if (!empty($filters['estatus_lead']['value'])) {
            $params['estatus_lead'] = $filters['estatus_lead']['value'];
        }

        return route('export.distribuidores', $params);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('exportar')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => $this->getExportUrl())
                ->openUrlInNewTab(),
        ];
    }
}
