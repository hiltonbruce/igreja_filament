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
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome do estado');
            $table->string('abbreviation', 2)->unique()->comment('Sigla do estado');
            $table->foreignId('country_id')->constrained('countries')->default(55)->comment('País do estado, padrão é Brasil');
            $table->foreignId('city_id')->nullable()->comment('Capital do estado');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
        // DB::statement("COMMENT ON TABLE states IS 'Finalidade: registrar estados
        //      Responsável: Bruce
        //      Versão: 1.0 - 03/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('states');
    }
};
