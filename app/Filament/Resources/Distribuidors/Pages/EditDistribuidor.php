<?php

namespace App\Filament\Resources\Distribuidors\Pages;

use App\Filament\Resources\Distribuidors\DistribuidorResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDistribuidor extends EditRecord
{
    protected static string $resource = DistribuidorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
