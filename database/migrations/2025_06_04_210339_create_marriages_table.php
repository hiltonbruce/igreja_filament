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
        Schema::create('marriages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->onDelete('cascade')
                ->comment('ID do membro associado ao casamento');

            $table->date('marriage_date')->nullable()->comment('Data do casamento do membro, se aplicável');
            $table->foreignId('spouse_member_id')->nullable()->constrained('members')->comment('ID do cônjuge, se aplicável');
            $table->string('spouse_name')->nullable()->comment('Nome do cônjuge do membro, se aplicável');
            $table->string('marriage_certificate')->nullable()->comment('Registro da certidão de casamento do membro, número, livro e folhas');
            $table->string('marriage_certificate_document')->nullable()->comment('Documento da certidão de casamento do membro, armazenado como URL ou caminho relativo');
            $table->date('marriage_date')->comment('Data do casamento');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
        DB::statement("COMMENT ON TABLE marriages IS 'Finalidade: registrar casamentos
            Responsável: Bruce
            Versão: 1.0 - 04/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marriages');
    }
};
