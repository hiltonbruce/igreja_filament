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
        Schema::create('clericals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->onDelete('cascade')
                ->comment('ID do membro associado ao cargo');
            $table->date('clerical_date')->nullable()->comment('Data do cargo do membro, se aplicável');
            $table->string('clerical_type')->nullable()->comment('Tipo de cargo do membro, se aplicável');
            $table->boolean('is_active')->default(true)->comment('Indica se o cargo está ativo');
            $table->foreignId('churche_id')
                ->constrained('churches')
                ->onDelete('cascade')
                ->comment('ID da igreja associada ao clerical');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
        DB::statement("COMMENT ON TABLE clericals IS 'Finalidade: registrar os cargos na igreja
            Responsável: Bruce
            Versão: 1.0 - 04/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clericals');
    }
};
