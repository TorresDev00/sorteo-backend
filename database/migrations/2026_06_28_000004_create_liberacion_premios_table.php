<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liberacion_premios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('liberacion_semanal_id')
                ->constrained('liberaciones_semanales')
                ->cascadeOnDelete();
            $table->foreignId('premio_id')
                ->constrained('premios')
                ->cascadeOnDelete();
            $table->integer('cantidad');
            $table->integer('cantidad_entregada')->default(0);
            // Premios asignados por el algoritmo pendientes de validación de factura.
            // Disponibilidad real = cantidad - cantidad_entregada - cantidad_reservada
            $table->integer('cantidad_reservada')->default(0);
            $table->timestamps();

            $table->unique(['liberacion_semanal_id', 'premio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liberacion_premios');
    }
};
