<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard; // <--- Importamos el Dashboard base

class Home extends BaseDashboard
{
    // 1. Cambia el título grande que sale dentro de la página
    protected static ?string $title = 'Home';

    // 2. Cambia el nombre que aparece en el menú lateral izquierdo
    public static function getNavigationLabel(): string
    {
        return 'Home';
    }
}