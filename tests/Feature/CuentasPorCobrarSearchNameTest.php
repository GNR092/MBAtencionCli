<?php

namespace Tests\Feature;

use App\Http\Controllers\CuentasPorCobrar;
use App\Http\Controllers\CuentasPorPagar;
use App\Http\Controllers\RetroactivoController;
use Tests\TestCase;

class CuentasPorCobrarSearchNameTest extends TestCase
{
    public function test_cuentas_por_cobrar_search_name_query_is_case_insensitive(): void
    {
        $controller = new CuentasPorCobrar();
        $reflection = new \ReflectionMethod($controller, 'aplicarFiltros');
        $reflection->setAccessible(true);

        $source = file_get_contents((new \ReflectionClass($controller))->getFileName());
        $this->assertStringContainsString(
            "whereRaw('LOWER(users.name) LIKE LOWER(?)'",
            $source,
            'CuentasPorCobrar::aplicarFiltros must use LOWER() for case-insensitive name search'
        );
    }

    public function test_cuentas_por_pagar_search_name_query_is_case_insensitive(): void
    {
        $files = [
            (new \ReflectionClass(new CuentasPorPagar()))->getFileName(),
            (new \ReflectionClass(new RetroactivoController()))->getFileName(),
        ];

        foreach ($files as $file) {
            $source = file_get_contents($file);
            $this->assertStringContainsString(
                "whereRaw('LOWER(users.name) LIKE LOWER(?)'",
                $source,
                "{$file} must use LOWER() for case-insensitive name search"
            );
            $this->assertStringNotContainsString(
                "\$q->where('users.name', 'LIKE'",
                $source,
                "{$file} must not contain raw LIKE on users.name (case-sensitive bug)"
            );
        }
    }
}
