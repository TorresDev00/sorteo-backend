<?php

namespace App\Filament\Resources\Distribuidors\Pages;

use App\Filament\Resources\Distribuidors\DistribuidorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDistribuidors extends ListRecords
{
    protected static string $resource = DistribuidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
