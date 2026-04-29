<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('birthday_templates', function (Blueprint $table) {
            $table->unsignedMediumInteger('canvas_width')->default(960)->after('overlay_images');
            $table->unsignedMediumInteger('canvas_height')->default(540)->after('canvas_width');
        });
    }

    public function down(): void
    {
        Schema::table('birthday_templates', function (Blueprint $table) {
            $table->dropColumn(['canvas_width', 'canvas_height']);
        });
    }
};