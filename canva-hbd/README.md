# Canva HBD — Happy Birthday Canvas Module

Editor visual drag-drop + envío automático de correos de cumpleaños para Laravel.

---

## Características

- 🎨 **Canvas editor** — Editor visual con componentes arrastrables (texto, imagen, botón, fondo, formas)
- 📧 **Variable `{nombre}`** — Componente especial que se reemplaza automáticamente por el nombre del cumpleañero
- ⏰ **Envío automático** — Comando `hbd:send` configurable por día/hora
- 🔧 **Envío manual** — Botón en cada tarjeta para enviar inmediatamente
- 📋 **Historial** — Registro de todos los correos enviados
- 📦 **Portable** — Módulo standalone como git submodule

---

## Instalación

### 1. Agregar como git submodule

```bash
git submodule add git@github.com:tu-usuario/canva-hbd.git canva-hbd
```

### 2. Autoload

En `composer.json` del proyecto host:

```json
"autoload": {
    "psr-4": {
        "Canva\\HBD\\": "canva-hbd/src/"
    }
}
```

Luego ejecutar:
```bash
composer dump-autoload
```

### 3. Migraciones

```bash
php artisan migrate
```

### 4. Seeder (crea plantilla y settings por defecto)

```bash
php artisan db:seed --class="Canva\\HBD\\Database\\Seeders\\HbdSeeder"
```

### 5. Rutas

En `routes/web.php` del proyecto host:

```php
require __DIR__.'/../canva-hbd/routes/hbd-routes.php';
```

### 6. Trait en User

En `app/Models/User.php`:

```php
use Canva\HBD\Traits\HasHbdBirthday;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasHbdBirthday;
    // ...
}
```

### 7. Scheduler (opcional, para envío automático)

En `app/Console/Kernel.php`:

```php
use Illuminate\Support\Facades\Schedule;

protected function schedule(Schedule $schedule): void
{
    $schedule->command('hbd:send')->dailyAt('09:00');
}
```

---

## Rutas del módulo

| Método | URI | Descripción |
|--------|-----|-------------|
| GET | `/hbd` | Lista de cumpleañeros |
| GET | `/hbd/settings` | Configuración (auto_send, hora, días) |
| POST | `/hbd/settings` | Guardar configuración |
| GET | `/hbd/canvas` | Editor visual de plantilla |
| POST | `/hbd/canvas` | Guardar plantilla |
| GET | `/hbd/canvas/preview` | Previsualización de la plantilla |
| POST | `/hbd/enviar/{userId}` | Enviar correo manualmente |
| POST | `/hbd/enviar-test/{userId}` | Enviar correo de prueba |
| POST | `/hbd/canvas/media` | Subir imagen al canvas |
| GET | `/hbd/historial` | Historial de envíos |

---

## Comandos Artisan

```bash
# Envío automático (ejecutado por el scheduler)
php artisan hbd:send

# Simular envío sin enviar realmente
php artisan hbd:send --dry-run
```

---

## Configuración

Crear archivo `.env` o agregar al `.env` del proyecto host:

```env
HBD_ADMIN_ROLE=administrador
HBD_USER_CLASS=App\\Models\\User
HBD_BIRTHDAY_FIELD=fecha_nacimiento
HBD_SEND_HOUR=09:00
HBD_AUTO_SEND=true
HBD_SEND_DAYS_BEFORE=0
```

---

## Estructura del Canvas (JSON)

El contenido de la plantilla se guarda como JSON en `hbd_templates.content`:

```json
{
  "desktop": [
    {
      "name": "Hero",
      "height": 500,
      "bgType": "color",
      "bgValue": "#1a1a2e",
      "components": [
        {
          "type": "text",
          "subtype": "heading",
          "top": 30,
          "left": 10,
          "width": 80,
          "content": "¡Feliz cumpleaños, {nombre}!",
          "color": "#ffffff",
          "fontSize": 48,
          "fontWeight": "bold",
          "textAlign": "center"
        }
      ]
    }
  ]
}
```

### Componentes disponibles

| Tipo | Propiedades |
|------|-------------|
| `text` | `content`, `color`, `fontSize`, `fontWeight`, `textAlign`, `fontFamily`, `subtype` (heading/body) |
| `image` | `url`, `height`, `width` |
| `button` | `text`, `href`, `bgColor`, `textColor`, `borderRadius` |
| `shape` | `shapeType` (rectangle/circle/rounded), `fill`, `width`, `height` |
| `bg` | `bgType` (color/image/gradient), `bgValue` |

### Posicionamiento

Todos los componentes usan posición porcentual (`top`, `left`, `width`, `height` en %).

---

## Integración en otros proyectos

1. Copiar el submodule `canva-hbd` a la carpeta del proyecto
2. Seguir pasos de Instalación (autoload, migrations, rutas, trait)
3. Opcionalmente ejecutar `publish/install.sh`

El módulo solo depende de:
- Laravel 10+
- Illuminate\Support\Facades\Mail
- Illuminate\Support\Facades\Auth
- Carbon\Carbon

No requiere paquetes adicionales. Usa Alpine.js y Tailwind via CDN en las vistas.

---

## Desinstalar

```bash
# Eliminar submodule
git submodule deinit canva-hbd
git rm canva-hbd

# Eliminar tablas (opcional)
php artisan migrate:rollback --path=canva-hbd/src/Database/Migrations
```
