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
        Schema::table('contract', function (Blueprint $table) {
            // Change 'contenido' to store a path instead of the file content
            $table->string('contenido')->change();

            // Add the new foreign key column
            $table->unsignedBigInteger('id_user_p')->nullable()->after('user_id');

            // Add the foreign key constraint
            $table->foreign('id_user_p')->references('id_user_p')->on('user_proyectos')->onDelete('set null');

            $table->dropColumn('proyecto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contract', function (Blueprint $table) {
            // Reverse the foreign key
            $table->dropForeign(['id_user_p']);
            $table->dropColumn('id_user_p');

            // Revert the 'contenido' column type. Data will be lost.
            $table->longText('contenido')->change();

            // Revert 'proyecto' column to not nullable if it was so before
            $table->string('proyecto')->nullable(false)->change();
        });
    }
};
