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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->comment('Primeiro nome do membro');
            $table->string('last_name')->comment('Sobrenome do membro');
            $table->date('birth_date')->nullable()->comment('Data de nascimento do membro');
            $table->foreignId('city_of_birth_id')->nullable()->constrained('cities')->comment('Cidade de nascimento do membro');
            $table->boolean('sex')->default(true)->comment('Sexo do membro, true para masculino e false para feminino');
            $table->boolean('donor')->default(false)->comment('Indica se o membro é doador de órgãos, padrão é false');
            $table->string('blood_type')->default('O+')->comment('Tipo sanguíneo do membro, padrão é O+');
            $table->string('photo')->nullable()->comment('Foto do membro, armazenada como URL ou caminho relativo');
            $table->string('mother_name')->nullable()->comment('Nome da mãe do membro');
            $table->foreignId('mother_member_id')->nullable()->constrained('members')->comment('ID do membro mãe, se aplicável');
            $table->string('father_name')->nullable()->comment('Nome do pai do membro');
            $table->foreignId('father_member_id')->nullable()->constrained('members')->comment('ID do membro pai, se aplicável');
            $table->boolean('matrimonial_status')->default(false)->comment('Estado civil do membro, true para casado(a) e false para solteiro(a)');
            $table->boolean('baptized_in_spirit')->default(false)->comment('Indica se o membro foi batizado no Espírito Santo, padrão é false');
            $table->date('baptized_in_spirit_date')->nullable()->comment('Data do batismo no Espírito Santo do membro');
            $table->date('baptized')->nullable()->comment('Data do batismo em água do membro');
            $table->foreignId('city_id')->nullable()->constrained('cities')->comment('Cidade do membro');
            $table->foreignId('state_id')->nullable()->constrained('states')->comment('Estado do membro');
            $table->foreignId('country_id')->constrained('countries')->default(55)->comment('País do membro, padrão é Brasil');
            $table->foreignId('neighborhood_id')->nullable()->constrained('neighborhoods')->comment('Bairro do membro');
            $table->string('number')->nullable()->comment('Número da residência do membro');
            $table->string('complement')->nullable()->comment('Complemento do endereço do membro');
            $table->string('document')->unique()->comment('Documento do membro, como CPF ou RG');
            $table->string('document_type')->default('CPF')->comment('Tipo de documento do membro, padrão é CPF');
            $table->string('email')->unique()->comment('Email do membro');
            $table->string('phone')->nullable()->comment('Telefone do membro');
            $table->string('address')->nullable()->comment('Endereço do membro');
            $table->integer('zip_code')->nullable()->comment('CEP do membro');
            $table->boolean('spiritual_situation')->default(true)->comment('Situação espiritual do membro - false para disciplinado');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        // DB::statement("COMMENT ON TABLE members IS 'Finalidade: registrar membros da igreja
        //     Responsável: Bruce
        //     Versão: 1.0 - 03/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
