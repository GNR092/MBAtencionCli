<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('cuentasporpagar')) {
            return;
        }

        Schema::table('cuentasporpagar', function (Blueprint $table) {
            if (! Schema::hasColumn('cuentasporpagar', 'id_user_depto')) {
                $table->unsignedBigInteger('id_user_depto')->nullable()->after('id_contract');
                $table->foreign('id_user_depto')->references('id_user_depto')->on('user_depto')->onDelete('set null');
                $table->index('id_user_depto', 'idx_cpp_user_depto');
            }

            if (! Schema::hasColumn('cuentasporpagar', 'origen')) {
                $table->string('origen', 20)->default('esperado')->after('id_user_depto');
                $table->index(['id_contract', 'mes_pago'], 'idx_cpp_contract_mes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('cuentasporpagar')) {
            return;
        }

        Schema::table('cuentasporpagar', function (Blueprint $table) {
            if (Schema::hasColumn('cuentasporpagar', 'origen')) {
                $table->dropIndex('idx_cpp_contract_mes');
                $table->dropColumn('origen');
            }

            if (Schema::hasColumn('cuentasporpagar', 'id_user_depto')) {
                $table->dropForeign(['id_user_depto']);
                $table->dropIndex('idx_cpp_user_depto');
                $table->dropColumn('id_user_depto');
            }
        });
    }
};
