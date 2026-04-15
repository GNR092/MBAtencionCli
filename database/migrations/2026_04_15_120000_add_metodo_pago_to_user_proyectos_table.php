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
            $table->string('metodo_pago', 50)->nullable();
        });

        DB::table('user_proyectos')
            ->select('id_user_p', 'id_user')
            ->orderBy('id_user_p')
            ->chunkById(500, function ($rows) {
                $userIds = collect($rows)
                    ->pluck('id_user')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if (empty($userIds)) {
                    return;
                }

                $methodsByUser = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->pluck('metodo_pago', 'id');

                foreach ($rows as $row) {
                    $metodoPago = $methodsByUser[$row->id_user] ?? null;

                    if (! $metodoPago) {
                        continue;
                    }

                    DB::table('user_proyectos')
                        ->where('id_user_p', $row->id_user_p)
                        ->whereNull('metodo_pago')
                        ->update(['metodo_pago' => $metodoPago]);
                }
            }, 'id_user_p');
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
