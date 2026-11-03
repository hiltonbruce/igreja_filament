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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nome do cargo (ex.: Secretário, Tesoureiro, Dirigente de Louvor)');
            $table->string('description')->nullable()->comment('Descrição do cargo');
            $table->boolean('supports_ordinal')->default(false)->comment('Indica se o cargo admite ordinal (1º, 2º, 3º...)');
            $table->string('scope', 10)->default('both')->comment('Onde o cargo pode ser indicado: church, unit ou both');
            $table->boolean('is_active')->default(true)->comment('Indica se o cargo está ativo');
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
        Schema::dropIfExists('positions');
    }
};
