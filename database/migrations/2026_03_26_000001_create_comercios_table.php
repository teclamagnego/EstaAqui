<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comercios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('whatsapp', 20);
            $table->string('zona_barrio');
            $table->string('direccion')->nullable();
            $table->string('categoria_comercio')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comercios');
    }
};
