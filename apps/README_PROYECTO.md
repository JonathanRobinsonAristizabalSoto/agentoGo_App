
# AgentoGo — Documentación (avance actual)

Plataforma SaaS multi negocio para gestión de turnos, reservas y automatización.

## 1) Estructura del proyecto

Este repo está organizado como **monorepo**:

- `apps/web` → Web (Next.js)
- `apps/mobile` → Móvil (Expo / React Native)
- `apps/api` → API (Laravel)

## 2) Qué se construyó hasta hoy

### 2.1 Web (Next.js)

- Se creó el proyecto dentro de `apps/web` con `create-next-app`.
- Configuración elegida:
	- TypeScript: Sí
	- ESLint: Sí
	- TailwindCSS: Sí
	- `src/` directory: Sí
	- App Router: Sí
	- Import alias: `@/*` (default)
- Se validó ejecución local viendo la pantalla inicial en el navegador.

**Cómo correr Web**

En una terminal:

```powershell
cd C:\Users\Aristizabal\OneDrive\Desktop\AgentoGo\apps\web
npm run dev
```

Abrir:
- http://localhost:3000

---

### 2.2 Mobile (Expo)

- Se creó el proyecto dentro de `apps/mobile` con `create-expo-app`.
- Se levantó Metro Bundler y se abrió en Android con Expo Go escaneando el QR.

**Problema resuelto (Expo Go incompatible)**

- Ocurrió el error: “Project is incompatible with this version of Expo Go”.
- Se resolvió ajustando/recreando el proyecto para que fuese compatible con la versión de Expo Go instalada o actualizando Expo Go.

**Cómo correr Mobile**

En una terminal:

```powershell
cd C:\Users\Aristizabal\OneDrive\Desktop\AgentoGo\apps\mobile
npx expo start
```

Luego:
- Abrir Expo Go en el celular (misma red Wi‑Fi) y escanear el QR.

---

### 2.3 API (Laravel 12)

- Se creó Laravel 12 dentro de `apps/api` con Composer.
- Se generó `.env`, se creó `APP_KEY` y se ejecutaron migraciones base.
- Se levantó el servidor y se validó la pantalla inicial de Laravel en el navegador.

**Problema resuelto (Composer sin ZIP)**

- Al inicio Composer avisó que no estaba disponible ZIP/unzip y descargaba “desde source” (lento).
- Se activó `extension=zip` en el `php.ini` correcto (XAMPP) y luego Composer pudo extraer paquetes como “Extracting archive”.

**Cómo correr API**

En una terminal:

```powershell
cd C:\Users\Aristizabal\OneDrive\Desktop\AgentoGo\apps\api
php artisan serve
```

Abrir:
- http://127.0.0.1:8000

## 3) Comandos útiles (Windows / PowerShell)

### 3.1 Ver archivos ocultos (cuando “parece vacío”)

```powershell
dir -Force
```

### 3.2 Borrar carpeta en PowerShell

> Nota: en PowerShell no sirve `rmdir /s /q` (eso es de CMD).

```powershell
Remove-Item -Recurse -Force nombre_carpeta
```

## 4) Estado actual

- Web: OK → http://localhost:3000
- Mobile: OK → Expo / QR / Expo Go
- API: OK (v0.2-dev) → http://127.0.0.1:8000
  - ✅ Autenticación multi-tenant con Sanctum
	- ✅ 49 tests passing
  - ✅ Documentación API actualizada

## 5) Cambios Recientes (Backend - v0.1-dev)

### API (apps/api/)

#### Implementado
- Middleware `EnsureTenant` para validar acceso a negocios por tenant
- Relación pivot `business_user` con columna `role`
- Header `X-Business-Id` para seleccionar contexto de negocio
- Factory `BusinessFactory::withOwner()` para tests
- Tests multi-tenant (3 tests nuevos)
- Documentación API (sección multi-tenant)
- README del backend con guía de inicio rápido
- GET /businesses con búsqueda por nombre/slug y filtro por estado
- Base de módulos reales: departamentos, empleados, clientes y reservas

#### Próximos Pasos (Backend)
1. Crear CRUDs y rutas para departamentos, empleados, clientes y reservas
2. Endurecimiento para producción: rate limiting y validaciones finales
3. CI/CD (tests automáticos)
4. Revisión final de documentación del monorepo

## Avances recientes (24-05-2026)

- Versión del backend: **v0.3-dev**
- Cambios clave implementados:
	- Middleware `SecurityHeaders` añadido y registrado en `AppServiceProvider`.
	- Ejemplos detallados de respuestas `403` y `422` añadidos en OpenAPI por endpoint.
	- `authorizeResource()` aplicado en controladores y políticas ajustadas.
	- Rate limiting básico y caching (Redis) configurados.
	- Documentación OpenAPI regenerada (`php artisan l5-swagger:generate`).
	- Suite de tests completa: **68 passed**.

## 6) Próximos Pasos Globales

### Backend (apps/api)
- Crear BusinessPolicy con lógica de autorización
- Implementar sistema de roles granular
- Documentar en OpenAPI

### Frontend (apps/web)
- Integrar con API de autenticación (Sanctum)
- Crear flujo de login/logout
- Dashboard multi-tenant

### Mobile (apps/mobile)
- Integrar con API
- Autenticación en app

