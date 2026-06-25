<?php

namespace App\Filament\Resources\LiberacionSemanals\Pages;

use App\Filament\Resources\LiberacionSemanals\LiberacionSemanalResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLiberacionSemanal extends EditRecord
{
    protected static string $resource = LiberacionSemanalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
