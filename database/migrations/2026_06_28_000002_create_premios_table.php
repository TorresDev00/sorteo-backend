<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sorteo_id')->constrained('sorteos')->cascadeOnDelete();
            $table->string('nombre', 191);
            $table->enum('tipo', ['electrodomesticos', 'merch', 'experiencia_de_marca']);
            $table->integer('cantidad_total');
            $table->integer('cantidad_disponible');
            $table->timestamps();

            $table->index(['sorteo_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premios');
    }
};
