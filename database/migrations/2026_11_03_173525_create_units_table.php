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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('church_id')
                ->constrained('churches')
                ->cascadeOnDelete()
                ->comment('Igreja à qual a unidade pertence');
            $table->foreignId('unit_type_id')
                ->constrained('unit_types')
                ->restrictOnDelete()
                ->comment('Tipo da unidade');
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete()
                ->comment('Unidade-pai (hierarquia dentro da igreja)');
            $table->string('name')->nullable()->comment('Nome específico da unidade; na ausência usa o nome do tipo');
            $table->string('description')->nullable()->comment('Descrição da unidade');
            $table->boolean('is_active')->default(true)->comment('Indica se a unidade está ativa');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('ID do usuário responsável pelo cadastro');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index('church_id');
            $table->index(['church_id', 'unit_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
