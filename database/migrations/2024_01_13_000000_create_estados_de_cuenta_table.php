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
        Schema::create('estados_de_cuenta', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('factura');
            $table->unsignedBigInteger('user_id'); // FK al usuario
            $table->boolean('is_admin')->default(false);
            $table->enum('proyecto', ['CAMPUS RESIDENCIA', 'ALDEA BORBOLETA I', 
            'ALDEA BORBOLETA II','ALDEA BORBOLETA III', 'TMZN 122', 'AVENUE TEMOZÓN',
            'MB RESORT MÉRIDA','GRAND TEMOZÓN','RUM','PRINCESS VILLAGE',
            'ROYAL SQUARE PLAZA', 'MB RESORT ORLANDO','MB WELLNESS RESORT',
            'SUBCONDOMINIO 1','SUBCONDOMINIO 2','SUBCONDOMINIO 3','SUBCONDOMINIO 5']);
            $table->date('fecha');
            $table->decimal('saldo',15,2);
            $table->decimal('importacion',15,2);
            $table->decimal('saldo_neto',15,2);

             // Clave foránea
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estados_de_cuenta');
    }
};
