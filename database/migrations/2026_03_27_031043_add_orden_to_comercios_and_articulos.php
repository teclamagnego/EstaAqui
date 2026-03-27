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
        Schema::table('comercios', function (Blueprint $table) {
            $table->integer('orden')->default(0)->after('activo');
        });
        Schema::table('articulos', function (Blueprint $table) {
            $table->integer('orden')->default(0)->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comercios', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
        Schema::table('articulos', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
    }
};
