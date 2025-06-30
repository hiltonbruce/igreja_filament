<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome da cidade');
            $table->foreignId('state_id')->constrained()->comment('Estado da cidade');
            $table->foreignId('country_id')->constrained('countries')->default(55)->comment('País da cidade, padrão é Brasil');
            $table->boolean('capital_city')->default(false)->comment('Indica se a cidade é a capital do estado');
            $table->string('ibge_code', 7)->unique()->comment('Código IBGE da cidade');
            $table->string('latitude', 10)->nullable()->comment('Latitude da cidade');
            $table->string('longitude', 10)->nullable()->comment('Longitude da cidade');
            $table->string('timezone')->default('America/Sao_Paulo')->comment('Fuso horário da cidade, padrão é America/Sao_Paulo');
            $table->string('area_code', 5)->nullable()->comment('Código de área da cidade');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
        DB::statement("COMMENT ON TABLE cities IS 'Finalidade: registrar cidades
             Responsável: Bruce
             Versão: 1.0 - 03/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
