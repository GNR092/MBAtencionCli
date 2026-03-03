<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('impuesto', function (Blueprint $table) {
            $table->decimal('tasaRetencion', 8, 4)->nullable()->after('tasaCuota');
        });
    }

    public function down(): void
    {
        Schema::table('impuesto', function (Blueprint $table) {
            $table->dropColumn('tasaRetencion');
        });
    }
};
