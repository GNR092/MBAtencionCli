<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user_depto')->nullable()->after('id_user_p');
            $table->foreign('id_user_depto')->references('id_user_depto')->on('user_depto')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('contract', function (Blueprint $table) {
            $table->dropForeign(['id_user_depto']);
            $table->dropColumn('id_user_depto');
        });
    }
};
