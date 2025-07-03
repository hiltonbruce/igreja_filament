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
        Schema::create('printed_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->onDelete('cascade')
                ->comment('ID do membro associado ao cargo');
            $table->foreignId('received_member_id')->constrained('members')->references('id')->onDelete('cascade')->comment('Quem recebeu o cartão para ser entregue');
            $table->foreignId('delivered_member_id')->constrained('members')->references('id')->onDelete('cascade')->comment('Quem entregou o cartão');
            $table->integer('receipt')->comment('Número do recibo');
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('ID do usuário responsável pelo cadastro');

            $table->timestampsTz();
            $table->softDeletesTz();
        });
        // DB::statement("COMMENT ON TABLE printed_cards IS 'Finalidade: registrar os cartões impressos dos membros
        //     Responsável: Bruce
        //     Versão: 1.0 - 04/06/2025';");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printed_cards');
    }
};
