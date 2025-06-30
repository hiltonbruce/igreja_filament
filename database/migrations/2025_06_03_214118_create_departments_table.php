<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nome do departamento');
            $table->string('description')->nullable()->comment('Descrição do departamento');
            $table->foreignId('church_id')
                ->constrained('churches')
                ->onDelete('cascade')
                ->comment('ID da igreja associada ao departamento');
            $table->foreignId('department_id')
                ->constrained('departments')
                ->onDelete('cascade')
                ->comment('ID do departamento pai, se houver');
            $table->foreignId('neighborhood_id')
                ->constrained('neighborhoods')
                ->onDelete('cascade')
                ->nullable()
                ->comment('Bairro do departamento, se aplicável');
            $table->string('address')->nullable()->comment('Endereço do departamento');
            $table->string('phone')->nullable()->comment('Telefone do departamento');
            $table->string('email')->nullable()->comment('Email do departamento');
            $table->string('number')->nullable()->comment('Número do departamento');
            $table->string('postal_code')->nullable()->comment('CEP do departamento');
            $table->string('website')->nullable()->comment('Website do departamento');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro do departamento');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unique([DB::raw('lower(name)'), 'church_id'], 'unique_department_name');
        });
        DB::statement("COMMENT ON TABLE departments IS 'Finalidade: registrar departamentos da igreja
            Responsável: Bruce
            Versão: 1.0 - 03/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
