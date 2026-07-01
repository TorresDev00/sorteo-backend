<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sorteo_id')->constrained('sorteos')->cascadeOnDelete();
            $table->foreignId('premio_id')
                ->nullable()
                ->constrained('premios')
                ->nullOnDelete();
            $table->foreignId('liberacion_premio_id')
                ->nullable()
                ->constrained('liberacion_premios')
                ->nullOnDelete();
            $table->string('cedula', 20);
            $table->string('nombre', 191);
            $table->string('telefono', 50);
            $table->text('direccion')->nullable();
            $table->string('lugar_compra', 191);
            $table->string('factura_imagen', 191);
            $table->integer('semana');
            $table->enum('estado', ['pendiente', 'preseleccionado', 'verificado', 'rechazado'])
                ->default('pendiente');
            $table->boolean('ganador')->default(false);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->timestamps();

            // Índices para lecturas frecuentes del algoritmo de sorteo
            $table->index('cedula');
            $table->index('estado');
            $table->index('semana');
            $table->index('ganador');
            $table->index(['sorteo_id', 'semana']);
            $table->index(['estado', 'ganador']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros');
    }
};
