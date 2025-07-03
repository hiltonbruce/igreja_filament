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
        Schema::create('clerical_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome do tipo de cargo');
            $table->string('description')->nullable()->comment('Descrição do tipo de cargo');
            $table->foreignId('clerical_type_id')
                ->nullable()
                ->constrained('clerical_types')
                ->onDelete('cascade')
                ->comment('ID do tipo de cargo pai, se houver');
            $table->timestamps();
            $table->softDeletesTz();
            $table->unique(['name', 'clerical_type_id'], 'unique_clerical_type_name');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
        });
        // DB::statement("COMMENT ON TABLE clerical_types IS 'Finalidade: registrar tipos de cargos
        //      Responsável: Bruce
        //      Versão: 1.0 - 04/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clerical_types');
    }
};
