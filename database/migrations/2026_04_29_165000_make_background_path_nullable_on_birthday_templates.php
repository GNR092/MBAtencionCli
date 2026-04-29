<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE birthday_templates ALTER COLUMN background_path DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE birthday_templates ALTER COLUMN background_path SET NOT NULL');
    }
};
