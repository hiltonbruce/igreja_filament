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
        Schema::create('churches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome da igreja');
            $table->string('denomination')->nullable()->comment('Denominação da igreja');
            $table->string('address')->nullable()->comment('Endereço da igreja');
            $table->string('phone')->nullable()->comment('Telefone da igreja');
            $table->string('email')->nullable()->comment('Email da igreja');
            $table->string('website')->nullable()->comment('Website da igreja');
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('neighborhood_id')->constrained('neighborhoods')->onDelete('cascade')->nullable()->comment('Bairro da igreja');
            $table->foreignId('state_id')->constrained('states')->onDelete('cascade')->comment('Estado da igreja');
            $table->foreignId('church_id')->constrained('churches')->onDelete('cascade')->nullable()->comment('ID da igreja pai, se houver');
            $table->mediumInteger('sector')->default(0)->comment('Setor da igreja, padrão é 0');
            $table->string('timezone')->default('America/Sao_Paulo')->comment('Fuso horário da igreja, padrão é America/Sao_Paulo');
            $table->string('latitude', 10)->nullable()->comment('Latitude da igreja');
            $table->string('longitude', 10)->nullable()->comment('Longitude da igreja');
            $table->string('area_code', 5)->nullable()->comment('Código de área da igreja');
            $table->string('postal_code', 10)->nullable()->comment('CEP da igreja');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('churches');
    }
};
