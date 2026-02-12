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
        Schema::create('file_logs', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->enum('file_type', ['xml', 'pdf']);
            $table->string('uuid')->nullable();
            $table->boolean('is_valid');
            $table->string('emisor_name')->nullable();
            $table->string('receptor_name')->nullable();
            $table->string('related_file')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_logs');
    }
};
