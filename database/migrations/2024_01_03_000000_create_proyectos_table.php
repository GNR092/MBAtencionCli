<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; 

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id('id_proyecto')->primary(); 
            
            $table->string('nombre_proyecto');
            $table->timestamps();
        });

        
        DB::table('proyectos')->insert([
            [
                'nombre_proyecto' => 'RESIDENT 1',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'RESIDENT 2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'CAMPUS RECIDENCIA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'TMZN 122',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [

                'nombre_proyecto' => 'GRAND TEMOZÓN',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'Aldea Borboleta I',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'Aldea Borboleta II',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'Aldea Borboleta III',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'MB RESORT MÉRIDA',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'Princess Village',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'Royal Square Plaza',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'RUM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'Avenue Temozón',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'MB Resort Orlando',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre_proyecto' => 'MB Wellness Resort',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};