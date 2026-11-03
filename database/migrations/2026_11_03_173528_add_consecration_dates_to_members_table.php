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
        Schema::table('members', function (Blueprint $table) {
            $table->date('auxiliary_consecrated_at')->nullable()->after('baptized')->comment('Data de consagração como Auxiliar de trabalho');
            $table->date('deacon_consecrated_at')->nullable()->after('auxiliary_consecrated_at')->comment('Data de consagração como Diácono');
            $table->date('presbyter_consecrated_at')->nullable()->after('deacon_consecrated_at')->comment('Data de consagração como Presbítero');
            $table->date('evangelist_consecrated_at')->nullable()->after('presbyter_consecrated_at')->comment('Data de consagração como Evangelista');
            $table->date('pastor_consecrated_at')->nullable()->after('evangelist_consecrated_at')->comment('Data de consagração como Pastor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'auxiliary_consecrated_at',
                'deacon_consecrated_at',
                'presbyter_consecrated_at',
                'evangelist_consecrated_at',
                'pastor_consecrated_at',
            ]);
        });
    }
};
