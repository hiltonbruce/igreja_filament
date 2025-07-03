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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome do país');
            $table->string('iso_code', 2)->unique()->comment('Código ISO do país');
            $table->string('phone_code', 5)->nullable()->comment('Código de telefone do país');
            $table->string('currency', 3)->nullable()->comment('Moeda do país');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique(['name', 'iso_code'], 'unique_country_name_iso');
        });
        // DB::statement("COMMENT ON TABLE countries IS 'Finalidade: registrar países
        //      Responsável: Bruce
        //      Versão: 1.0 - 03/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
