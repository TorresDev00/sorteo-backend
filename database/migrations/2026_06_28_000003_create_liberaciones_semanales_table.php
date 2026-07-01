<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liberaciones_semanales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sorteo_id')->constrained('sorteos')->cascadeOnDelete();
            $table->integer('semana');
            $table->timestamp('fecha_liberacion')->useCurrent();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['sorteo_id', 'semana']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liberaciones_semanales');
    }
};
