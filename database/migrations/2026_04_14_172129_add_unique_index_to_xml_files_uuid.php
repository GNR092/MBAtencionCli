<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('xml_files') || ! Schema::hasColumn('xml_files', 'uuid')) {
            return;
        }

        $duplicateExists = DB::table('xml_files')
            ->selectRaw('LOWER(uuid) as normalized_uuid, COUNT(*) as total')
            ->whereNotNull('uuid')
            ->where('uuid', '!=', '')
            ->groupBy('normalized_uuid')
            ->having('total', '>', 1)
            ->exists();

        if ($duplicateExists) {
            return;
        }

        $dbName = DB::getDatabaseName();
        $indexes = DB::table('information_schema.statistics')
            ->where('table_schema', $dbName)
            ->where('table_name', 'xml_files')
            ->selectRaw('INDEX_NAME as index_name')
            ->pluck('index_name')
            ->unique()
            ->values()
            ->all();

        if (! in_array('xml_files_uuid_unique', $indexes, true)) {
            Schema::table('xml_files', function (Blueprint $table) {
                $table->unique('uuid', 'xml_files_uuid_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('xml_files')) {
            return;
        }

        $dbName = DB::getDatabaseName();
        $indexes = DB::table('information_schema.statistics')
            ->where('table_schema', $dbName)
            ->where('table_name', 'xml_files')
            ->selectRaw('INDEX_NAME as index_name')
            ->pluck('index_name')
            ->unique()
            ->values()
            ->all();

        if (in_array('xml_files_uuid_unique', $indexes, true)) {
            Schema::table('xml_files', function (Blueprint $table) {
                $table->dropUnique('xml_files_uuid_unique');
            });
        }
    }
};
