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
        Schema::create('complementaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->onDelete('cascade')
                ->comment('ID do membro associado ao casamento');
            $table->string('denomination_name')->comment('Nome da denominação que veio');
            $table->date('denomination_date')->comment('Data em que foi recebido, aclamado ou transferido');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');
            $table->text('obs')->comment('Observações ou pendências');
            $table->timestamps();
            $table->softDeletesTz();
        });
        // DB::statement("COMMENT ON TABLE complementaries IS 'Finalidade: registrar os complementares do membro
        //     Responsável: Bruce
        //     Versão: 1.0 - 04/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complementaries');
    }
};
