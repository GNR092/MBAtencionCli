<?php

namespace Tests\Feature;

use App\Models\Anuncio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDashboardAnunciosTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dashboard_shows_active_anuncios_in_expected_priority_order(): void
    {
        $user = User::factory()->create([
            'role' => 'usuario',
        ]);

        Anuncio::create([
            'titulo' => 'Anuncio prioridad baja',
            'descripcion' => 'Baja',
            'estado' => 'activo',
            'prioridad' => 'baja',
        ]);

        Anuncio::create([
            'titulo' => 'Anuncio prioridad alta',
            'descripcion' => 'Alta',
            'estado' => 'activo',
            'prioridad' => 'alta',
        ]);

        Anuncio::create([
            'titulo' => 'Anuncio prioridad media',
            'descripcion' => 'Media',
            'estado' => 'activo',
            'prioridad' => 'media',
        ]);

        Anuncio::create([
            'titulo' => 'Anuncio inactivo',
            'descripcion' => 'No debe verse',
            'estado' => 'inactivo',
            'prioridad' => 'alta',
        ]);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertOk();
        $response->assertViewHas('anuncios', function ($anuncios) {
            return $anuncios->pluck('titulo')->all() === [
                'Anuncio prioridad alta',
                'Anuncio prioridad media',
                'Anuncio prioridad baja',
            ];
        });
    }
}
