#!/bin/bash

echo "=============================================="
echo "  Instalador del módulo Canva HBD"
echo "=============================================="

echo ""
echo "[1/5] Copiando migraciones..."
cp -r src/Database/Migrations/* database/migrations/

echo "[2/5] Ejecutando migraciones..."
php artisan migrate

echo ""
echo "[3/5] Ejecutando seeder..."
php artisan db:seed --class=Canva\\HBD\\Database\\Seeders\\HbdSeeder

echo ""
echo "[4/5] Agregando trait HasHbdBirthday al modelo User..."
echo "      Añade la siguiente línea a tu modelo User:"
echo ""
echo "      use Canva\\HBD\\Traits\\HasHbdBirthday;"
echo "      (dentro de la clase, junto a los otros traits)"
echo ""

echo "[5/5] Incluyendo rutas en web.php..."
echo ""
echo "      Añade esta línea en tu archivo routes/web.php:"
echo ""
echo "      require __DIR__.'/../canva-hbd/routes/hbd-routes.php';"
echo ""

echo "=============================================="
echo "  ¡Instalación completada!"
echo "=============================================="
echo ""
echo "  Pasos finales:"
echo "  1. Añade el trait HasHbdBirthday al modelo User"
echo "  2. Añade las rutas en web.php"
echo "  3. Ejecuta: php artisan hbd:send --dry-run"
echo "     para verificar que todo funciona"
echo ""
