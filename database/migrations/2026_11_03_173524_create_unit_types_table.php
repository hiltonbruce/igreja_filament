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
        Schema::create('unit_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nome do tipo de unidade (ex.: Círculo de Orações, Louvor, Secretaria)');
            $table->string('description')->nullable()->comment('Descrição do tipo de unidade');
            $table->boolean('is_active')->default(true)->comment('Indica se o tipo de unidade está ativo');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_types');
    }
};
