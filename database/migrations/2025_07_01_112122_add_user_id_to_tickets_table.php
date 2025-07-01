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
        Schema::table('tickets', function (Blueprint $table) {
            // Agregar campo user_id después del campo status
            $table->unsignedBigInteger('user_id')->nullable()->after('status');
            
            // Crear la foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Opcional: Agregar índice para mejorar performance en consultas
            $table->index(['area_id', 'user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Eliminar el índice primero
            $table->dropIndex(['area_id', 'user_id', 'status']);
            
            // Eliminar la foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Eliminar la columna
            $table->dropColumn('user_id');
        });
    }
};
