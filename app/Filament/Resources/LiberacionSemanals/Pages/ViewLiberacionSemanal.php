<?php

namespace App\Filament\Resources\LiberacionSemanals\Pages;

use App\Filament\Resources\LiberacionSemanals\LiberacionSemanalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLiberacionSemanal extends ViewRecord
{
    protected static string $resource = LiberacionSemanalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
