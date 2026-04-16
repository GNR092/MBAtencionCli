<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cuentasporpagar')) {
            return;
        }

        Schema::table('cuentasporpagar', function (Blueprint $table) {
            if (! Schema::hasIndex('cuentasporpagar', ['mes_pago', 'origen'], 'index')) {
                $table->index(['mes_pago', 'origen'], 'idx_cpp_mes_origen');
            }

            if (! Schema::hasIndex('cuentasporpagar', ['mes_pago', 'es_retroactivo', 'estado'], 'index')) {
                $table->index(['mes_pago', 'es_retroactivo', 'estado'], 'idx_cpp_mes_retro_estado');
            }

            if (! Schema::hasIndex('cuentasporpagar', ['mes_pago', 'estado'], 'index')) {
                $table->index(['mes_pago', 'estado'], 'idx_cpp_mes_estado');
            }
        });

        $invalidOrigenCount = DB::table('cuentasporpagar')
            ->whereNotIn('origen', ['xml', 'esperado'])
            ->count();

        if ($invalidOrigenCount > 0) {
            throw new RuntimeException('No se pudo agregar CHECK de origen: hay registros invalidos en cuentasporpagar.origen.');
        }

        $invalidMesPagoCount = DB::table('cuentasporpagar')
            ->whereNotNull('mes_pago')
            ->whereRaw("mes_pago !~ '^[0-9]{4}-(0[1-9]|1[0-2])$'")
            ->count();

        if ($invalidMesPagoCount > 0) {
            throw new RuntimeException('No se pudo agregar CHECK de mes_pago: hay valores con formato invalido en cuentasporpagar.mes_pago.');
        }

        DB::statement('ALTER TABLE cuentasporpagar DROP CONSTRAINT IF EXISTS chk_cpp_origen_valid');
        DB::statement("ALTER TABLE cuentasporpagar ADD CONSTRAINT chk_cpp_origen_valid CHECK (origen IN ('xml', 'esperado'))");

        DB::statement('ALTER TABLE cuentasporpagar DROP CONSTRAINT IF EXISTS chk_cpp_mes_pago_format');
        DB::statement("ALTER TABLE cuentasporpagar ADD CONSTRAINT chk_cpp_mes_pago_format CHECK (mes_pago IS NULL OR mes_pago ~ '^[0-9]{4}-(0[1-9]|1[0-2])$')");
    }

    public function down(): void
    {
        if (! Schema::hasTable('cuentasporpagar')) {
            return;
        }

        DB::statement('ALTER TABLE cuentasporpagar DROP CONSTRAINT IF EXISTS chk_cpp_origen_valid');
        DB::statement('ALTER TABLE cuentasporpagar DROP CONSTRAINT IF EXISTS chk_cpp_mes_pago_format');

        Schema::table('cuentasporpagar', function (Blueprint $table) {
            if (Schema::hasIndex('cuentasporpagar', ['mes_pago', 'origen'], 'index')) {
                $table->dropIndex('idx_cpp_mes_origen');
            }

            if (Schema::hasIndex('cuentasporpagar', ['mes_pago', 'es_retroactivo', 'estado'], 'index')) {
                $table->dropIndex('idx_cpp_mes_retro_estado');
            }

            if (Schema::hasIndex('cuentasporpagar', ['mes_pago', 'estado'], 'index')) {
                $table->dropIndex('idx_cpp_mes_estado');
            }
        });
    }
};
