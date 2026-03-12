<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_efectivo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_contract');
            $table->foreign('id_contract')->references('id')->on('contract')->onDelete('cascade');
            $table->unsignedBigInteger('id_cuentas_por_pagar')->nullable();
            $table->foreign('id_cuentas_por_pagar')->references('id_cuentas_por_pagar')->on('cuentasporpagar')->onDelete('set null');
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->decimal('monto', 15, 2);
            $table->date('fecha_pago');
            $table->string('mes_pago', 7);
            $table->string('concepto', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_efectivo');
    }
};
