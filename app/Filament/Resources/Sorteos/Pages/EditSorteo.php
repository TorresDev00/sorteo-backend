<?php

namespace App\Filament\Resources\Sorteos\Pages;

use App\Filament\Resources\Sorteos\SorteoResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditSorteo extends EditRecord
{
    protected static string $resource = SorteoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
