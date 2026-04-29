<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birthday_templates', function (Blueprint $table) {
            $table->json('overlay_images')->nullable()->after('zones_json');
        });
    }

    public function down(): void
    {
        Schema::table('birthday_templates', function (Blueprint $table) {
            $table->dropColumn('overlay_images');
        });
    }
};