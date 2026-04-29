<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('background_path');
            $table->json('zones_json');
            $table->text('default_message')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_templates');
    }
};
