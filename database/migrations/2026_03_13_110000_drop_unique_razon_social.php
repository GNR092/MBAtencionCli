<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE proyectos DROP FOREIGN KEY proyectos_id_razon_social_foreign');
        DB::statement('ALTER TABLE proyectos DROP INDEX proyectos_id_razon_social_unique');
        DB::statement('ALTER TABLE proyectos CHANGE COLUMN id_razon_social id_razon_social BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE proyectos ADD CONSTRAINT proyectos_id_razon_social_foreign FOREIGN KEY (id_razon_social) REFERENCES razones_sociales (id_razon_social) ON DELETE SET NULL');
    }

    public function down(): void
    {
    }
};