<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('member_spiritual_situations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')
                ->constrained('members')
                ->cascadeOnDelete()
                ->comment('Membro a quem a ocorrência se refere');
            $table->string('situation')
                ->comment('Situação espiritual: disciplined, in_communion, deceased, changed_church, withdrew, transferred');
            $table->foreignId('directed_by_member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete()
                ->comment('Membro que dirigiu o culto em que a ocorrência foi registrada');
            $table->date('recorded_at')->comment('Data do registro da ocorrência');
            $table->unsignedInteger('discipline_days')
                ->nullable()
                ->comment('Tempo de disciplina em dias; nulo significa prazo indeterminado');
            $table->timestampsTz();

            $table->index(['member_id', 'recorded_at']);
        });

        if (Schema::hasColumn('members', 'spiritual_situation')) {
            DB::table('members')
                ->where('spiritual_situation', false)
                ->lazyById()
                ->each(function (object $member): void {
                    $recordedAt = $member->updated_at ?? $member->created_at ?? now();

                    DB::table('member_spiritual_situations')->insert([
                        'member_id' => $member->id,
                        'situation' => 'disciplined',
                        'directed_by_member_id' => null,
                        'recorded_at' => Carbon::parse($recordedAt)->toDateString(),
                        'discipline_days' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });

            Schema::table('members', function (Blueprint $table) {
                $table->dropColumn('spiritual_situation');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_spiritual_situations');

        if (! Schema::hasColumn('members', 'spiritual_situation')) {
            Schema::table('members', function (Blueprint $table) {
                $table->boolean('spiritual_situation')
                    ->default(true)
                    ->comment('Situação espiritual do membro - false para disciplinado');
            });
        }
    }
};
