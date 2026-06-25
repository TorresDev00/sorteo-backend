<?php

namespace App\Filament\Resources\Sorteos\Pages;

use App\Filament\Resources\Sorteos\SorteoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSorteo extends ViewRecord
{
    protected static string $resource = SorteoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
