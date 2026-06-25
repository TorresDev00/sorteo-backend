<?php

namespace App\Filament\Resources\LiberacionSemanals\Pages;

use App\Filament\Resources\LiberacionSemanals\LiberacionSemanalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLiberacionSemanals extends ListRecords
{
    protected static string $resource = LiberacionSemanalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
