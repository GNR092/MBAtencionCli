@echo off
echo ============================================
echo   LIMPIEZA DE BASE DE DATOS
echo   Mb_Cyn - Atencion CLI
echo ============================================
echo.

echo ADVERTENCIA: Esto eliminara todos los datos de:
echo   - xml_batches
echo   - xml_files
echo   - cuentasporpagar
echo   - impuesto
echo   - pagos_efectivo
echo   - estados_de_cuenta
echo   - factura_validacions
echo   - incrementos_importe
echo   - logos
echo.
echo Se conservaran:
echo   - users, contract, proyectos
echo   - razones_sociales, regimen_fiscals
echo   - user_proyectos, user_depto
echo.

set /p confirmacion="¿Esta seguro de continuar? (SI/NO): "
if /i not "%confirmacion%"=="SI" goto :cancelado

echo.
echo Ejecutando limpieza...
echo.

cd /d "%~dp0"

php artisan tinker --execute="DB::statement('SET FOREIGN_KEY_CHECKS=0'); DB::statement('TRUNCATE TABLE xml_batches'); DB::statement('TRUNCATE TABLE xml_files'); DB::statement('TRUNCATE TABLE cuentasporpagar'); DB::statement('TRUNCATE TABLE impuesto'); DB::statement('TRUNCATE TABLE pagos_efectivo'); DB::statement('TRUNCATE TABLE estados_de_cuenta'); DB::statement('TRUNCATE TABLE factura_validacions'); DB::statement('TRUNCATE TABLE incrementos_importe'); DB::statement('TRUNCATE TABLE logos'); DB::statement('SET FOREIGN_KEY_CHECKS=1');"

echo.
echo Eliminando archivos fisicos...
echo.

php artisan tinker --execute="Storage::deleteDirectory('xml_files'); Storage::deleteDirectory('pdf_files');"

echo.
echo ============================================
echo   LIMPIEZA COMPLETADA
echo ============================================
echo.
php artisan tinker --execute="echo 'xml_batches: ' . DB::table('xml_batches')->count() . PHP_EOL; echo 'xml_files: ' . DB::table('xml_files')->count() . PHP_EOL; echo 'cuentasporpagar: ' . DB::table('cuentasporpagar')->count() . PHP_EOL; echo 'impuesto: ' . DB::table('impuesto')->count() . PHP_EOL; echo 'logos: ' . DB::table('logos')->count() . PHP_EOL; echo 'users: ' . DB::table('users')->count() . PHP_EOL; echo 'contract: ' . DB::table('contract')->count() . PHP_EOL; echo 'razones_sociales: ' . DB::table('razones_sociales')->count() . PHP_EOL;"
echo.
pause
exit /b 0

:cancelado
echo.
echo Operacion cancelada.
echo.
pause
exit /b 1
