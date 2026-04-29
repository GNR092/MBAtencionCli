<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('birthday_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('birthday_templates')->cascadeOnDelete();
            $table->date('birthday_date');
            $table->dateTime('scheduled_for');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending')->index();
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'birthday_date']);
            $table->index(['birthday_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('birthday_deliveries');
    }
};
