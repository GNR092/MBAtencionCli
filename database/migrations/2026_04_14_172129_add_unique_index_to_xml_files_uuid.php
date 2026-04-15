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
            ->select('uuid')
            ->whereNotNull('uuid')
            ->where('uuid', '!=', '')
            ->groupBy('uuid')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicateExists) {
            return;
        }

        Schema::table('xml_files', function (Blueprint $table) {
            if (!Schema::hasIndex('xml_files', ['uuid'], 'unique')) {
                $table->unique('uuid', 'xml_files_uuid_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('xml_files', function (Blueprint $table) {
            if (Schema::hasIndex('xml_files', ['uuid'], 'unique')) {
                $table->dropUnique('xml_files_uuid_unique');
            }
        });
    }
};
