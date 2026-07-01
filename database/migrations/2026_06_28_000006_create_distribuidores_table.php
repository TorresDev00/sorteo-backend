<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distribuidores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial', 191);
            $table->string('email', 191)->unique();
            $table->string('telefono', 50);
            $table->string('estado_ubicacion', 100);
            $table->text('mensaje')->nullable();
            $table->enum('estatus_lead', ['nuevo', 'contactado', 'rechazado'])->default('nuevo');
            $table->text('notas_administrador')->nullable();
            $table->timestamps();

            $table->index(['estatus_lead', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribuidores');
    }
};
