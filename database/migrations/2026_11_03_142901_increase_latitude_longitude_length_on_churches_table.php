<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('churches', function (Blueprint $table): void {
            $table->string('latitude', 20)->nullable()->comment('Latitude da igreja')->change();
            $table->string('longitude', 20)->nullable()->comment('Longitude da igreja')->change();
        });
    }

    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table): void {
            $table->string('latitude', 10)->nullable()->comment('Latitude da igreja')->change();
            $table->string('longitude', 10)->nullable()->comment('Longitude da igreja')->change();
        });
    }
};
