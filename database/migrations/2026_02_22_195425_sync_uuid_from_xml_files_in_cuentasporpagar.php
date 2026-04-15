<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reemplazar UUIDs aleatorios con el folio fiscal real del CFDI, usando el Query Builder para compatibilidad.
        DB::table('cuentasporpagar as c')
            ->join('xml_files as x', 'c.xml_file_id', '=', 'x.id')
            ->whereNotNull('x.uuid')
            ->where('x.uuid', '!=', '')
            ->update(['c.uuid' => DB::raw('x.uuid')]);
    }

    public function down(): void {}
};
