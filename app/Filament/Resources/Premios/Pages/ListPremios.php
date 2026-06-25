<?php

namespace App\Filament\Resources\Premios\Pages;

use App\Filament\Resources\Premios\PremioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPremios extends ListRecords
{
    protected static string $resource = PremioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
