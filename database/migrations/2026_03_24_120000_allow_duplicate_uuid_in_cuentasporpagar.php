<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            if (Schema::hasIndex('cuentasporpagar', ['uuid'], 'unique')) {
                $table->dropUnique('cuentasporpagar_uuid_unique');
            }

            if (!Schema::hasIndex('cuentasporpagar', ['uuid'], 'index')) {
                $table->index('uuid', 'idx_cuentasporpagar_uuid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cuentasporpagar', function (Blueprint $table) {
            if (Schema::hasIndex('cuentasporpagar', ['uuid'], 'index')) {
                $table->dropIndex('idx_cuentasporpagar_uuid');
            }

            if (!Schema::hasIndex('cuentasporpagar', ['uuid'], 'unique')) {
                $table->unique('uuid', 'cuentasporpagar_uuid_unique');
            }
        });
    }
};
