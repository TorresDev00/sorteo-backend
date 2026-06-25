<?php

namespace App\Filament\Resources\Premios\Pages;

use App\Filament\Resources\Premios\PremioResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPremio extends ViewRecord
{
    protected static string $resource = PremioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
