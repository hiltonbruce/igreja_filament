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
        Schema::table('churches', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['neighborhood_id']);
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->renameColumn('church_id', 'parent_id');
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->string('level', 20)->default('municipal')->after('name')->comment('Nível hierárquico da igreja');
            $table->string('region', 20)->nullable()->after('level')->comment('Macrorregião, obrigatória para sede regional');
            $table->foreignId('country_id')->nullable()->after('website')->constrained('countries')->nullOnDelete();

            $table->unsignedBigInteger('city_id')->nullable()->change();
            $table->unsignedBigInteger('state_id')->nullable()->change();
            $table->unsignedBigInteger('neighborhood_id')->nullable()->change();
            $table->unsignedBigInteger('parent_id')->nullable()->change();

            $table->foreign('parent_id')->references('id')->on('churches')->nullOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->nullOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->nullOnDelete();
            $table->foreign('neighborhood_id')->references('id')->on('neighborhoods')->nullOnDelete();

            $table->index('level');
            $table->index(['city_id', 'level']);
        });

        DB::table('churches')->whereNull('level')->update(['level' => 'municipal']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['state_id']);
            $table->dropForeign(['neighborhood_id']);
            $table->dropForeign(['country_id']);
            $table->dropIndex(['level']);
            $table->dropIndex(['city_id', 'level']);
            $table->dropColumn(['level', 'region', 'country_id']);
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->renameColumn('parent_id', 'church_id');
        });

        Schema::table('churches', function (Blueprint $table) {
            $table->foreign('church_id')->references('id')->on('churches')->cascadeOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
            $table->foreign('state_id')->references('id')->on('states')->cascadeOnDelete();
            $table->foreign('neighborhood_id')->references('id')->on('neighborhoods')->cascadeOnDelete();
        });
    }
};
