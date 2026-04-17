@echo off
setlocal

cd /d "%~dp0\.."

echo ============================================
echo   Laravel refresh local (Windows)
echo ============================================
echo Proyecto: %CD%
echo.

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP no esta instalado o no esta en el PATH.
    exit /b 1
)

echo [1/5] php artisan optimize
php artisan migrate
if errorlevel 1 goto :error

echo [2/5] php artisan optimize
php artisan optimize
if errorlevel 1 goto :error

echo [3/5] php artisan view:cache
php artisan view:cache
if errorlevel 1 goto :error

echo [4/5] php artisan cache:clear
php artisan cache:clear
if errorlevel 1 goto :error

echo [5/5] php artisan storage:link
php artisan storage:link
if errorlevel 1 (
    echo [WARN] storage:link fallo ^(puede que el link ya exista^). Continuando...
)

echo.
echo Listo.
exit /b 0

:error
echo.
echo [ERROR] Fallo uno de los comandos de Artisan.
exit /b 1
