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
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome do bairro');
            $table->foreignId('city_id')->constrained('cities')->comment('Cidade do bairro');
            $table->foreignId('state_id')->constrained('states')->comment('Estado do bairro');
            $table->foreignId('country_id')->constrained('countries')->default(55)->comment('País do bairro, padrão é Brasil');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
        DB::statement("COMMENT ON TABLE neighborhoods IS 'Finalidade: registrar bairros
             Responsável: Bruce
             Versão: 1.0 - 03/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
    }
};
