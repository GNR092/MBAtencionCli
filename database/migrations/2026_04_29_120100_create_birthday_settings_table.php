<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_settings', function (Blueprint $table) {
            $table->id();
            $table->time('send_time')->default('09:00:00');
            $table->string('timezone')->default('America/Mexico_City');
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->unsignedSmallInteger('retry_minutes')->default(10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_settings');
    }
};
