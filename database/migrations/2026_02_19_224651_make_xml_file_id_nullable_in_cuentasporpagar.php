<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->dropForeign(['xml_file_id']);
            $table->unsignedBigInteger('xml_file_id')->nullable()->change();
            $table->foreign('xml_file_id')->references('id')->on('xml_files')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            $table->dropForeign(['xml_file_id']);
            $table->unsignedBigInteger('xml_file_id')->nullable(false)->change();
            $table->foreign('xml_file_id')->references('id')->on('xml_files')->onDelete('cascade');
        });
    }
};
