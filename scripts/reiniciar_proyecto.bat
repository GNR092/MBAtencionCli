@echo off
setlocal

cd /d "%~dp0\.."

set "SEED_ARG="
if /i "%~1"=="--seed" set "SEED_ARG=--seed"

echo ============================================
echo   REINICIO TOTAL DEL PROYECTO
echo ============================================
echo.
echo Este proceso hara lo siguiente:
echo   1) php artisan migrate:fresh %SEED_ARG%
echo   2) php artisan optimize:clear
echo   3) Limpiar archivos en storage
echo.
echo ADVERTENCIA: Se eliminaran datos de base y archivos guardados.
echo.

set /p confirmacion="Escribe SI para continuar: "
if /i not "%confirmacion%"=="SI" goto :cancelado

echo.
echo [1/3] Reiniciando base de datos...
php artisan migrate:fresh %SEED_ARG%
if errorlevel 1 goto :error

echo.
echo [2/3] Limpiando caches...
php artisan optimize:clear
if errorlevel 1 goto :error

echo.
echo [3/3] Limpiando storage...
for %%D in (
    "storage\logs"
    "storage\framework\cache"
    "storage\framework\sessions"
    "storage\framework\testing"
    "storage\framework\views"
    "storage\app\tmp"
    "storage\app\private"
    "storage\app\public"
) do (
    if exist "%%~D" (
        powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-ChildItem -LiteralPath '%%~D' -Force ^| Where-Object { $_.Name -ne '.gitignore' } ^| Remove-Item -Recurse -Force -ErrorAction SilentlyContinue"
    )
)

echo.
echo ============================================
echo   PROCESO COMPLETADO
echo ============================================
echo.
echo Puedes ejecutar:
echo   scripts\reiniciar_proyecto.bat
echo o con seeders:
echo   scripts\reiniciar_proyecto.bat --seed
echo.
exit /b 0

:cancelado
echo.
echo Operacion cancelada.
echo.
exit /b 1

:error
echo.
echo Ocurrio un error durante el proceso.
echo.
exit /b 1
