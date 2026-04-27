<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hbd_sents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hbd_template_id')->nullable()->constrained('hbd_templates')->nullOnDelete();
            $table->date('sent_date');
            $table->string('recipient_email');
            $table->text('rendered_html')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'sent_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hbd_sents');
    }
};
