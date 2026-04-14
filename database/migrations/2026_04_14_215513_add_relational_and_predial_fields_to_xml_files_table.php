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
        if (! Schema::hasTable('xml_files')) {
            return;
        }

        Schema::table('xml_files', function (Blueprint $table) {
            if (! Schema::hasColumn('xml_files', 'id_contract')) {
                $table->unsignedBigInteger('id_contract')->nullable()->after('id_proyecto');
                $table->foreign('id_contract')->references('id')->on('contract')->onDelete('set null');
                $table->index(['id_contract', 'mes'], 'idx_xml_files_contract_mes');
            }

            if (! Schema::hasColumn('xml_files', 'id_user_depto')) {
                $table->unsignedBigInteger('id_user_depto')->nullable()->after('id_contract');
                $table->foreign('id_user_depto')->references('id_user_depto')->on('user_depto')->onDelete('set null');
                $table->index('id_user_depto', 'idx_xml_files_user_depto');
            }

            if (! Schema::hasColumn('xml_files', 'predial_xml')) {
                $table->string('predial_xml', 50)->nullable()->after('departamento');
            }

            if (! Schema::hasColumn('xml_files', 'predial_status')) {
                $table->string('predial_status', 20)->default('no_validado')->after('predial_xml');
                $table->index('predial_status', 'idx_xml_files_predial_status');
            }

            if (! Schema::hasColumn('xml_files', 'predial_observacion')) {
                $table->string('predial_observacion')->nullable()->after('predial_status');
            }

            if (! Schema::hasColumn('xml_files', 'validation_flags')) {
                $table->json('validation_flags')->nullable()->after('validation_errors');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('xml_files')) {
            return;
        }

        Schema::table('xml_files', function (Blueprint $table) {
            if (Schema::hasColumn('xml_files', 'validation_flags')) {
                $table->dropColumn('validation_flags');
            }

            if (Schema::hasColumn('xml_files', 'predial_observacion')) {
                $table->dropColumn('predial_observacion');
            }

            if (Schema::hasColumn('xml_files', 'predial_status')) {
                $table->dropIndex('idx_xml_files_predial_status');
                $table->dropColumn('predial_status');
            }

            if (Schema::hasColumn('xml_files', 'predial_xml')) {
                $table->dropColumn('predial_xml');
            }

            if (Schema::hasColumn('xml_files', 'id_user_depto')) {
                $table->dropForeign(['id_user_depto']);
                $table->dropIndex('idx_xml_files_user_depto');
                $table->dropColumn('id_user_depto');
            }

            if (Schema::hasColumn('xml_files', 'id_contract')) {
                $table->dropForeign(['id_contract']);
                $table->dropIndex('idx_xml_files_contract_mes');
                $table->dropColumn('id_contract');
            }
        });
    }
};
