<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hbd_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_send')->default(true);
            $table->integer('send_days_before')->default(0);
            $table->string('send_hour', 5)->default('09:00');
            $table->string('subject_template')->default('¡Feliz cumpleaños, {nombre}!');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hbd_settings');
    }
};
