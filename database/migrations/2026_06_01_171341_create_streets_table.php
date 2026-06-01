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
        Schema::create('streets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome da rua');
            $table->string('zip_code', 8)->nullable()->index()->comment('CEP da rua, apenas dígitos (sanitizado)');
            $table->decimal('latitude', 10, 7)->nullable()->comment('Latitude da rua (pode ser nulo)');
            $table->decimal('longitude', 10, 7)->nullable()->comment('Longitude da rua (pode ser nulo)');
            $table->foreignId('neighborhood_id')->constrained('neighborhoods')->comment('Bairro da rua');
            $table->foreignId('city_id')->constrained('cities')->comment('Cidade da rua');
            $table->foreignId('state_id')->constrained('states')->comment('Estado da rua');
            $table->foreignId('country_id')->constrained('countries')->default(55)->comment('País da rua, padrão é Brasil');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
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
        Schema::dropIfExists('streets');
    }
};
