<?php

namespace App\Filament\Resources\Registros\Pages;

use App\Filament\Resources\Registros\RegistroResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRegistros extends ListRecords
{
    protected static string $resource = RegistroResource::class;

    public function getExportUrl(): string
    {
        $params = [];
        $filters = $this->tableFilters ?? [];

        if (!empty($filters['estado']['value'])) {
            $params['estado'] = $filters['estado']['value'];
        }

        if (!empty($filters['semana']['value'])) {
            $params['semana'] = $filters['semana']['value'];
        }

        return route('export.registros', $params);
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
