<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reemplazar UUIDs aleatorios con el folio fiscal real del CFDI
        DB::statement("
            UPDATE cuentasporpagar c
            INNER JOIN xml_files x ON c.xml_file_id = x.id
            SET c.uuid = x.uuid
            WHERE x.uuid IS NOT NULL AND x.uuid != ''
        ");
    }

    public function down(): void {}
};
