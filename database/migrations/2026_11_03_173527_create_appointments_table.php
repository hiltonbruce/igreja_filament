<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete()
                ->comment('Membro indicado ao cargo');
            $table->foreignId('position_id')
                ->constrained('positions')
                ->restrictOnDelete()
                ->comment('Cargo indicado');
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete()
                ->comment('Igreja de contexto da indicação');
            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('units')
                ->cascadeOnDelete()
                ->comment('Unidade da indicação, quando o cargo for de unidade');
            $table->unsignedSmallInteger('ordinal')->nullable()->comment('Ordinal do cargo na unidade/igreja (1º, 2º, 3º...)');
            $table->date('started_at')->nullable()->comment('Início da indicação');
            $table->date('ended_at')->nullable()->comment('Término da indicação (nulo quando vigente)');
            $table->boolean('is_active')->default(true)->comment('Indica se a indicação está ativa');
            $table->string('notes')->nullable()->comment('Observações da indicação');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('member_id');
            $table->index(['church_id', 'position_id']);
            $table->index(['unit_id', 'position_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
