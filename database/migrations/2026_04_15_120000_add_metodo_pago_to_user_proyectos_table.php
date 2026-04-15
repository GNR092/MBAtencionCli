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
        Schema::table('user_proyectos', function (Blueprint $table) {
            $table->string('metodo_pago', 50)->nullable()->after('id_proyecto');
        });

        DB::table('user_proyectos')
            ->join('users', 'users.id', '=', 'user_proyectos.id_user')
            ->whereNull('user_proyectos.metodo_pago')
            ->update([
                'user_proyectos.metodo_pago' => DB::raw('users.metodo_pago'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_proyectos', function (Blueprint $table) {
            $table->dropColumn('metodo_pago');
        });
    }
};
