@echo off
ECHO Iniciando servicios de desarrollo de Laravel...
ECHO.

REM Iniciar Vite en un bucle para que se reinicie si falla
ECHO Iniciando Vite (npm run dev)...
start "Vite" /MIN cmd /c "for /L %%a in () do (npm run dev)"

REM Iniciar el servidor de Artisan en un bucle para que se reinicie si falla
ECHO Iniciando Servidor Artisan (php artisan serve)...
start "Artisan" /MIN cmd /c "for /L %%a in () do (php artisan serve)"

ECHO.
ECHO Los servicios de Vite y Artisan se han iniciado en ventanas minimizadas.
ECHO Se reiniciarán automáticamente si se cierran.
ECHO.
ECHO Presiona cualquier tecla para detener todos los servicios.
pause > nul

ECHO.
ECHO Deteniendo servicios...

REM Detener las ventanas de Vite y Artisan (y todos sus procesos hijos)
taskkill /FI "WINDOWTITLE eq Vite" /T /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq Artisan" /T /F >nul 2>&1

ECHO.
ECHO Servicios detenidos.
pause
