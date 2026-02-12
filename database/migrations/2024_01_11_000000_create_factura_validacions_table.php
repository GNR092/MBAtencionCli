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
        Schema::create('factura_validacions', function (Blueprint $table) {
        $table->id();
        $table->string('uuid')->nullable();
        $table->string('emisor_name')->nullable();
        $table->string('receptor_name')->nullable();
        $table->boolean('valid')->default(false);
        $table->json('errors')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factura_validacions');
    }
};
