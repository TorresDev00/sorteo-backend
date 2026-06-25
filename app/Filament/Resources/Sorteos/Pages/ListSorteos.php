<?php

namespace App\Filament\Resources\Sorteos\Pages;

use App\Filament\Resources\Sorteos\SorteoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSorteos extends ListRecords
{
    protected static string $resource = SorteoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
