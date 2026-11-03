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
        Schema::table('clericals', function (Blueprint $table) {
            $table->dropForeign(['churche_id']);
        });

        Schema::table('clericals', function (Blueprint $table) {
            $table->renameColumn('churche_id', 'church_id');
        });

        Schema::table('clericals', function (Blueprint $table) {
            $table->foreign('church_id')->references('id')->on('churches')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clericals', function (Blueprint $table) {
            $table->dropForeign(['church_id']);
        });

        Schema::table('clericals', function (Blueprint $table) {
            $table->renameColumn('church_id', 'churche_id');
        });

        Schema::table('clericals', function (Blueprint $table) {
            $table->foreign('churche_id')->references('id')->on('churches')->cascadeOnDelete();
        });
    }
};
