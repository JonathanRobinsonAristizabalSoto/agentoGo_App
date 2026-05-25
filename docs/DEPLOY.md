# Despliegue (Deploy) - Guía rápida

Esta guía explica cómo desplegar la API (`apps/api`) en un servidor de producción. Explicación simple, paso a paso.

Requisitos mínimos:
- PHP >= 8.2 con ext: pdo, pdo_mysql (o pdo_pgsql), mbstring, openssl, zip
- Composer
- Base de datos (MySQL/Postgres) accesible desde el servidor
- Redis (opcional, recomendado para cache/colas)
- Nginx + php-fpm o Apache con mod_php

Pasos básicos (Linux, servidor remoto):

1. Clonar el repo y moverse a la carpeta de la API

```bash
git clone <repo-url> --depth=1
cd AgentoGo/apps/api
```

2. Instalar dependencias

```bash
composer install --no-dev --optimize-autoloader
```

3. Configurar `.env` de producción

- Copiar `.env.example` a `.env` y rellenar variables: `APP_ENV=production`, `APP_KEY`, `DB_*`, `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `SENTRY_DSN`, etc.

4. Generar `APP_KEY` y migrar la base de datos

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force    # solo si aplica
```

5. Preparar storage, caches y optimizaciones

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

6. Colas y workers

Usar `supervisor` (Linux) o `systemd` para ejecutar el worker. Ejemplo de archivo `supervisord.conf`:

[program:agento-queue]
command=php /path/to/apps/api/artisan queue:work --sleep=3 --tries=3 --timeout=90
process_name=%(program_name)s_%(process_num)02d
numprocs=2
autostart=true
autorestart=true
user=www-data
redirect_stderr=true

7. Webserver (Nginx) - ejemplo mínimo

server {
    listen 80;
    server_name api.example.com;
    root /path/to/apps/api/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}

8. Healthchecks y monitoreo

- Configurar Sentry, métricas y alertas.
- Exponer una ruta de healthcheck (ej: `/health`) protegida por IP o token.

9. CI/CD recomendado

- Ejecutar pipeline que:
  - Ejecuta tests (`php artisan test`)
  - Genera docs OpenAPI (`php artisan l5-swagger:generate`)
  - Construye artefactos y despliega (rsync / scp / container image)

10. Rollback básico

- Mantener migraciones que puedan revertirse o usar snapshots de DB. Para rollback:

```bash
php artisan migrate:rollback --step=1
```

Notas finales:
- Nunca pongas credenciales en el repo.
- Usa entornos separados (staging / production).
