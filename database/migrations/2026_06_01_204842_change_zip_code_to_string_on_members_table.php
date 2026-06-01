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
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE members ALTER COLUMN zip_code TYPE VARCHAR(255) USING zip_code::varchar');
            DB::statement("COMMENT ON COLUMN members.zip_code IS 'CEP/código postal do membro'");

            return;
        }

        Schema::table('members', function (Blueprint $table) {
            $table->string('zip_code')->nullable()->comment('CEP/código postal do membro')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE members ALTER COLUMN zip_code TYPE INTEGER USING NULLIF(regexp_replace(zip_code, '\\D', '', 'g'), '')::integer");
            DB::statement("COMMENT ON COLUMN members.zip_code IS 'CEP do membro'");

            return;
        }

        Schema::table('members', function (Blueprint $table) {
            $table->integer('zip_code')->nullable()->comment('CEP do membro')->change();
        });
    }
};
