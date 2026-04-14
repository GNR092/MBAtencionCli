<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('xml_files') || ! Schema::hasTable('contract') || ! Schema::hasTable('user_proyectos')) {
            return;
        }

        DB::table('xml_files')
            ->whereNull('predial_status')
            ->update(['predial_status' => 'no_validado']);

        DB::table('xml_files')
            ->whereNull('predial_xml')
            ->update(['predial_status' => 'sin_predial']);

        DB::table('xml_files')
            ->whereNotNull('predial_xml')
            ->whereIn('predial_status', ['no_validado', 'sin_predial'])
            ->update(['predial_status' => 'no_validado']);

        DB::table('xml_files')
            ->select('id', 'id_user', 'id_proyecto')
            ->whereNotNull('id_user')
            ->whereNotNull('id_proyecto')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $userProyecto = DB::table('user_proyectos')
                        ->where('id_user', $row->id_user)
                        ->where('id_proyecto', $row->id_proyecto)
                        ->first(['id_user_p']);

                    if (! $userProyecto) {
                        continue;
                    }

                    $contract = DB::table('contract')
                        ->where('id_user_p', $userProyecto->id_user_p)
                        ->orderByDesc('id')
                        ->first(['id', 'id_user_depto']);

                    if (! $contract) {
                        continue;
                    }

                    DB::table('xml_files')
                        ->where('id', $row->id)
                        ->update([
                            'id_contract' => $contract->id,
                            'id_user_depto' => $contract->id_user_depto,
                        ]);
                }
            });

        if (Schema::hasTable('cuentasporpagar')) {
            DB::statement('UPDATE cuentasporpagar c INNER JOIN contract ct ON ct.id = c.id_contract SET c.id_user_depto = ct.id_user_depto WHERE c.id_user_depto IS NULL');
            DB::statement("UPDATE cuentasporpagar SET origen = CASE WHEN xml_file_id IS NOT NULL THEN 'xml' ELSE 'esperado' END WHERE origen IS NULL OR origen = ''");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('xml_files')) {
            DB::table('xml_files')->update([
                'id_contract' => null,
                'id_user_depto' => null,
                'predial_status' => 'no_validado',
            ]);
        }

        if (Schema::hasTable('cuentasporpagar')) {
            DB::table('cuentasporpagar')->update([
                'id_user_depto' => null,
                'origen' => 'esperado',
            ]);
        }
    }
};
