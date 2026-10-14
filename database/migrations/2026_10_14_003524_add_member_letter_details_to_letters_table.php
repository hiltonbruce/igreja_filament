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
        Schema::table('letters', function (Blueprint $table) {
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete()
                ->comment('Membro a quem a carta se refere');
            $table->string('type')
                ->comment('Tipo da carta: recommendation, change, in_transit');
            $table->date('issued_at')->comment('Data da carta');
            $table->string('destination')->comment('Igreja de destino da carta');
            $table->foreignId('directed_by_member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete()
                ->comment('Membro que dirigiu o culto em que a carta foi emitida');
            $table->foreignId('member_spiritual_situation_id')
                ->nullable()
                ->unique()
                ->constrained('member_spiritual_situations')
                ->nullOnDelete()
                ->comment('Situação de transferido gerada automaticamente pela carta de mudança');

            $table->index(['member_id', 'issued_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropIndex(['member_id', 'issued_at']);
            $table->dropConstrainedForeignId('member_spiritual_situation_id');
            $table->dropConstrainedForeignId('directed_by_member_id');
            $table->dropConstrainedForeignId('member_id');
            $table->dropColumn(['type', 'issued_at', 'destination']);
        });
    }
};
