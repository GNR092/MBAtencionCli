<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM cuentasporpagar'))->pluck('Key_name')->all();

        if (in_array('cuentasporpagar_uuid_unique', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->dropUnique('cuentasporpagar_uuid_unique');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM cuentasporpagar'))->pluck('Key_name')->all();
        if (! in_array('idx_cuentasporpagar_uuid', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->index('uuid', 'idx_cuentasporpagar_uuid');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM cuentasporpagar'))->pluck('Key_name')->all();

        if (in_array('idx_cuentasporpagar_uuid', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->dropIndex('idx_cuentasporpagar_uuid');
            });
        }

        $indexes = collect(DB::select('SHOW INDEX FROM cuentasporpagar'))->pluck('Key_name')->all();
        if (! in_array('cuentasporpagar_uuid_unique', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->unique('uuid', 'cuentasporpagar_uuid_unique');
            });
        }
    }
};
