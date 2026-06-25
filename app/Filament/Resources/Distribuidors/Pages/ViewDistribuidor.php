<?php

namespace App\Filament\Resources\Distribuidors\Pages;

use App\Filament\Resources\Distribuidors\DistribuidorResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDistribuidor extends ViewRecord
{
    protected static string $resource = DistribuidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
