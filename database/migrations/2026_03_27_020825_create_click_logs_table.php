<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('click_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo'); // E.g., 'vista_comercio', 'click_whatsapp_articulo', 'click_whatsapp_comercio'
            $table->foreignId('comercio_id')->constrained('comercios')->onDelete('cascade');
            $table->foreignId('articulo_id')->nullable()->constrained('articulos')->onDelete('cascade');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('city')->nullable(); // Geolocation info if available
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('click_logs');
    }
};
