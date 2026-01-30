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
        Schema::create('xml_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('xml_batches')->onDelete('cascade');
            $table->string('filename');
             $table->unsignedBigInteger('id_user'); // FK al usuario
            $table->string('uuid')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->json('validation_errors')->nullable();
            $table->string('emisor_name')->nullable();
            $table->string('receptor_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('pdf_filename')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('pdf_uploaded')->default(false);
            $table->string('departamento')->nullable();
            $table->string('mes')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xml_files');
    }
};
