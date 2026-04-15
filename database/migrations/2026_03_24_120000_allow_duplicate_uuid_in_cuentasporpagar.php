<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = $this->getIndexNames();

        if (in_array('cuentasporpagar_uuid_unique', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->dropUnique('cuentasporpagar_uuid_unique');
            });
        }

        $indexes = $this->getIndexNames();
        if (! in_array('idx_cuentasporpagar_uuid', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->index('uuid', 'idx_cuentasporpagar_uuid');
            });
        }
    }

    public function down(): void
    {
        $indexes = $this->getIndexNames();

        if (in_array('idx_cuentasporpagar_uuid', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->dropIndex('idx_cuentasporpagar_uuid');
            });
        }

        $indexes = $this->getIndexNames();
        if (! in_array('cuentasporpagar_uuid_unique', $indexes, true)) {
            Schema::table('cuentasporpagar', function (Blueprint $table) {
                $table->unique('uuid', 'cuentasporpagar_uuid_unique');
            });
        }
    }

    private function getIndexNames(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            return collect(DB::select(
                'SELECT indexname FROM pg_indexes WHERE schemaname = current_schema() AND tablename = ?',
                ['cuentasporpagar']
            ))->pluck('indexname')->all();
        }

        return collect(DB::select('SHOW INDEX FROM cuentasporpagar'))
            ->pluck('Key_name')
            ->all();
    }
};
