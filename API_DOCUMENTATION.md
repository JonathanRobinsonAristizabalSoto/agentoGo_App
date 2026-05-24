# API AgentoGo - Documentación

## Descripción General

API REST construida con Laravel 12 que proporciona autenticación con tokens (Sanctum), gestión multi-tenant de negocios, autorización basada en roles, y paginación.

### Características Principales
- ✅ **Autenticación:** Bearer tokens con Laravel Sanctum
- ✅ **Multi-tenant:** Middleware de tenant + roles (owner, editor, viewer)
- ✅ **Autorización:** BusinessPolicy con control de acceso basado en roles
- ✅ **Validación:** FormRequests centralizados con mensajes en español
- ✅ **Paginación:** Parámetros `per_page` (default: 15) y `page`
- ✅ **Tests:** 30 passing (201 assertions, 0 warnings)

### Estado de la API: v0.2-dev

**Base URL:** `http://127.0.0.1:8000/api` (desarrollo)

### Cambios en v0.2
- ✅ FormRequests centralizados (validación en un solo lugar)
- ✅ BusinessPolicy para autorización basada en roles
- ✅ Paginación en GET /businesses (per_page, page)
- ✅ Mensajes de error en español
- ✅ 30 tests con 201 assertions, sin warnings

### Tabla de Contenidos
1. [Configuración](#configuración)
2. [Autenticación](#autenticación)
3. [Endpoints de Autenticación](#endpoints-de-autenticación)
4. [Endpoints de Negocios](#endpoints-de-negocios)
5. [Respuestas de Error](#respuestas-de-error)
6. [Ejemplos cURL](#ejemplos-curl)
7. [Multi-tenant](#multi-tenant-tenancy)
8. [Notas de Seguridad](#notas-de-seguridad)

---

## Configuración

### Requisitos
- PHP 8.2+
- Composer
- SQLite o MySQL
- Node.js (para Vite)

### Instalación

```bash
# Clonar el repositorio
git clone <repo-url>

# Instalar dependencias
composer install

# Copiar archivo de entorno
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# (Opcional) Sembrar datos de ejemplo
php artisan db:seed
```

## Autenticación

La API utiliza **Laravel Sanctum** para autenticación con tokens. Todos los endpoints protegidos requieren el header:

```
Authorization: Bearer {token}
```

---

## Endpoints de Autenticación

### 1. Registro de Usuario

**POST** `/api/auth/register`

Crea un nuevo usuario en el sistema.

**Request:**
```json
{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "password": "password123"
}
```

**Validación:**
- `name`: requerido, string, máximo 120 caracteres
- `email`: requerido, email válido, máximo 190 caracteres, único
- `password`: requerido, string, mínimo 8 caracteres

**Response (201):**
```json
{
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "created_at": "2026-05-22T12:00:00.000000Z",
    "updated_at": "2026-05-22T12:00:00.000000Z"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

---

### 2. Iniciar Sesión

**POST** `/api/auth/login`

Autentica un usuario existente y retorna un token.

**Request:**
```json
{
  "email": "juan@example.com",
  "password": "password123"
}
```

**Validación:**
- `email`: requerido, email válido
- `password`: requerido, string

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "created_at": "2026-05-22T12:00:00.000000Z",
    "updated_at": "2026-05-22T12:00:00.000000Z"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

**Response (422) - Credenciales inválidas:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Credenciales inválidas."]
  }
}
```

---

### 3. Obtener Perfil del Usuario

**GET** `/api/me` ⚠️ *Requiere autenticación*

Retorna los datos del usuario autenticado.

**Request Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "created_at": "2026-05-22T12:00:00.000000Z",
    "updated_at": "2026-05-22T12:00:00.000000Z"
  }
}
```

---

### 4. Cerrar Sesión

**POST** `/api/auth/logout` ⚠️ *Requiere autenticación*

Invalida el token actual y cierra la sesión.

**Request Headers:**
```
Authorization: Bearer {token}
```

**Response (200):**
```json
{
  "ok": true
}
```

---

## Endpoints de Negocios

### 1. Listar Negocios del Usuario

**GET** `/api/businesses` ⚠️ *Requiere autenticación*

Obtiene todos los negocios asociados al usuario autenticado con paginación, ordenados por ID descendente.

**Request Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `per_page` (opcional): Número de items por página. Default: 15. Máximo: 100
- `page` (opcional): Número de página. Default: 1. Mínimo: 1

**Ejemplos:**
```
GET /api/businesses                    # Página 1, 15 items por página
GET /api/businesses?per_page=10        # Página 1, 10 items por página
GET /api/businesses?per_page=10&page=2 # Página 2, 10 items por página
```

**Response (200):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Tech Solutions",
      "slug": "tech-solutions",
      "industry_id": null,
      "logo": null,
      "primary_color": "#007BFF",
      "secondary_color": "#6C757D",
      "timezone": "America/Bogota",
      "status": "active",
      "created_at": "2026-05-22T12:00:00.000000Z",
      "updated_at": "2026-05-22T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 25,
    "per_page": 15,
    "current_page": 1,
    "last_page": 2,
    "from": 1,
    "to": 15,
    "has_more_pages": true
  }
}
```

**Errores:**
- `422`: Validación fallida (ej: per_page > 100)

---

### 2. Crear Nuevo Negocio

**POST** `/api/businesses` ⚠️ *Requiere autenticación*

Crea un nuevo negocio y lo asocia al usuario autenticado como propietario.

**Request:**
```json
{
  "name": "Mi Nuevo Negocio",
  "slug": "mi-nuevo-negocio",
  "timezone": "America/Bogota",
  "primary_color": "#FF5733",
  "secondary_color": "#33FF57"
}
```

**Validación:**
- `name`: requerido, string, máximo 190 caracteres
- `slug`: opcional, string, máximo 190 caracteres, alfanumérico con guiones (alpha_dash)
- `timezone`: opcional, string, máximo 64 caracteres
- `primary_color`: opcional, string, máximo 32 caracteres
- `secondary_color`: opcional, string, máximo 32 caracteres

**Comportamiento:**
- Si no se proporciona `slug`, se genera automáticamente a partir del `name`
- El `slug` debe ser único. Si existe, se añade un contador automáticamente
- El negocio se crea con estado `active` por defecto
- El usuario se asocia como `owner` automáticamente

**Response (201):**
```json
{
  "data": {
    "id": 1,
    "name": "Mi Nuevo Negocio",
    "slug": "mi-nuevo-negocio",
    "industry_id": null,
    "logo": null,
    "primary_color": "#FF5733",
    "secondary_color": "#33FF57",
    "timezone": "America/Bogota",
    "status": "active",
    "created_at": "2026-05-22T12:00:00.000000Z",
    "updated_at": "2026-05-22T12:00:00.000000Z"
  }
}
```

---

### 3. Obtener Negocio Específico

**GET** `/api/businesses/{id}` ⚠️ *Requiere autenticación*

Retorna los datos de un negocio específico. El usuario debe tener acceso al negocio.

**Request Headers:**
```
Authorization: Bearer {token}
```

**URL Parameters:**
- `id` (required): ID del negocio

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Tech Solutions",
    "slug": "tech-solutions",
    "industry_id": null,
    "logo": null,
    "primary_color": "#007BFF",
    "secondary_color": "#6C757D",
    "timezone": "America/Bogota",
    "status": "active",
    "created_at": "2026-05-22T12:00:00.000000Z",
    "updated_at": "2026-05-22T12:00:00.000000Z"
  }
}
```

**Response (403) - Sin acceso:**
```json
{
  "message": "No autorizado"
}
```

---

### 4. Actualizar Negocio

**PUT** `/api/businesses/{id}` ⚠️ *Requiere autenticación*

Actualiza los datos de un negocio. El usuario debe tener acceso al negocio.

**Request:**
```json
{
  "name": "Tech Solutions Actualizado",
  "timezone": "America/Mexico_City",
  "primary_color": "#FF0000"
}
```

**Validación:**
- `name`: opcional, string, máximo 190 caracteres
- `timezone`: opcional, nullable, string, máximo 64 caracteres
- `primary_color`: opcional, nullable, string, máximo 32 caracteres
- `secondary_color`: opcional, nullable, string, máximo 32 caracteres

**Response (200):**
```json
{
  "data": {
    "id": 1,
    "name": "Tech Solutions Actualizado",
    "slug": "tech-solutions",
    "timezone": "America/Mexico_City",
    "primary_color": "#FF0000",
    "secondary_color": "#6C757D",
    "status": "active",
    "created_at": "2026-05-22T12:00:00.000000Z",
    "updated_at": "2026-05-22T12:00:00.000000Z"
  }
}
```

**Response (403) - Sin acceso:**
```json
{
  "message": "No autorizado"
}
```

---

### 5. Eliminar Negocio

**DELETE** `/api/businesses/{id}` ⚠️ *Requiere autenticación*

Elimina un negocio. El usuario debe tener acceso al negocio.

**Request Headers:**
```
Authorization: Bearer {token}
```

**URL Parameters:**
- `id` (required): ID del negocio

**Response (204):**
Sin contenido

**Response (403) - Sin acceso:**
```json
{
  "message": "No autorizado"
}
```

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | Solicitud exitosa |
| 201 | Recurso creado exitosamente |
| 204 | Recurso eliminado exitosamente (sin contenido) |
| 401 | No autenticado o token inválido |
| 403 | No autorizado para acceder al recurso |
| 404 | Recurso no encontrado |
| 422 | Datos de validación inválidos |
| 500 | Error interno del servidor |

---

## Errores Comunes

### Token expirado o inválido
```json
{
  "message": "Unauthenticated."
}
```

### Validación fallida
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["El email es requerido."],
    "password": ["La contraseña debe tener al menos 8 caracteres."]
  }
}
```

### Acceso denegado
```json
{
  "message": "No autorizado"
}
```

---

## Testing

Ejecutar tests:

```bash
# Todos los tests
php artisan test

# Con salida detallada
php artisan test --testdox

# Tests específicos
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/BusinessTest.php
```

---

## Ejemplos de Uso

### Con cURL

**Registro:**
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Juan","email":"juan@example.com","password":"password123"}'
```

**Login:**
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"juan@example.com","password":"password123"}'
```

**Listar negocios (página 1, 15 items):**
```bash
curl -X GET http://localhost:8000/api/businesses \
  -H "Authorization: Bearer {token}"
```

**Listar negocios con paginación (página 2, 10 items):**
```bash
curl -X GET "http://localhost:8000/api/businesses?per_page=10&page=2" \
  -H "Authorization: Bearer {token}"
```

**Crear negocio:**
```bash
curl -X POST http://localhost:8000/api/businesses \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"name":"Mi Negocio","timezone":"America/Bogota","primary_color":"#007BFF"}'
```

**Obtener negocio específico:**
```bash
curl -X GET http://localhost:8000/api/businesses/1 \
  -H "Authorization: Bearer {token}"
```

**Actualizar negocio:**
```bash
curl -X PUT http://localhost:8000/api/businesses/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"name":"Nuevo Nombre","primary_color":"#FF0000"}'
```

**Eliminar negocio:**
```bash
curl -X DELETE http://localhost:8000/api/businesses/1 \
  -H "Authorization: Bearer {token}"
```

---

## Endpoints de Auditoría

### Auditoría automática de cambios

Todos los cambios (create, update, delete) en negocios se registran automáticamente en la tabla `audit_logs` con:
- `user_id`: ID del usuario que realizó el cambio
- `model_type`: Tipo de modelo (ej: `App\Models\Business`)
- `model_id`: ID del modelo modificado
- `action`: Tipo de acción (created, updated, deleted)
- `old_values`: JSON con valores anteriores (solo en updates/deletes)
- `new_values`: JSON con valores nuevos (solo en creates/updates)
- `ip_address`: IP del cliente que hizo la request
- `user_agent`: User-Agent del cliente
- `created_at`: Timestamp del evento

### Listar Logs de Auditoría

**GET** `/api/audit-logs` ⚠️ *Requiere autenticación*

Obtiene el historial de cambios con filtros y paginación.

**Parámetros de Query:**
- `per_page` (opcional): Items por página (default: 15, máximo: 100)
- `page` (opcional): Número de página (default: 1)
- `model_type` (opcional): Filtrar por tipo de modelo (ej: `App\Models\Business`)
- `model_id` (opcional): Filtrar por ID del modelo
- `action` (opcional): Filtrar por acción (`created`, `updated`, `deleted`)
- `user_id` (opcional): Filtrar por ID del usuario que realizó el cambio
- `date_from` (opcional): Fecha desde (formato: YYYY-MM-DD)
- `date_to` (opcional): Fecha hasta (formato: YYYY-MM-DD)

**Response 200 OK:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "model_type": "App\\Models\\Business",
      "model_id": 5,
      "action": "updated",
      "old_values": {
        "name": "Mi Negocio",
        "status": "active"
      },
      "new_values": {
        "name": "Mi Negocio Actualizado",
        "status": "inactive"
      },
      "ip_address": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2026-05-22T12:00:00.000000Z",
      "updated_at": "2026-05-22T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 25,
    "per_page": 15,
    "current_page": 1,
    "last_page": 2,
    "from": 1,
    "to": 15,
    "has_more_pages": true
  }
}
```

**Ejemplos:**

Listar todos los logs (página 1, 15 items):
```bash
curl -X GET http://localhost:8000/api/audit-logs \
  -H "Authorization: Bearer {token}"
```

Filtrar por acción (updated):
```bash
curl -X GET "http://localhost:8000/api/audit-logs?action=updated" \
  -H "Authorization: Bearer {token}"
```

Filtrar por modelo y ID:
```bash
curl -X GET "http://localhost:8000/api/audit-logs?model_type=App\\Models\\Business&model_id=5" \
  -H "Authorization: Bearer {token}"
```

Filtrar por usuario y rango de fechas:
```bash
curl -X GET "http://localhost:8000/api/audit-logs?user_id=1&date_from=2026-05-20&date_to=2026-05-22" \
  -H "Authorization: Bearer {token}"
```

Página 2 con 10 items:
```bash
curl -X GET "http://localhost:8000/api/audit-logs?per_page=10&page=2" \
  -H "Authorization: Bearer {token}"
```

**Errores:**
- `422`: Validación fallida (ej: per_page > 100, action inválida, user_id no existe)

---

## Respuestas de Error

### Errores Comunes

**401 - No Autenticado**
```json
{
  "message": "Unauthenticated."
}
```

**403 - No Autorizado**
```json
{
  "message": "This action is unauthorized."
}
```

**404 - No Encontrado**
```json
{
  "message": "No query results for model [App\\Models\\Business] 1"
}
```

**422 - Validación Fallida**
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["El nombre del negocio es requerido"],
    "email": ["El email debe ser válido"]
  }
}
```

### Códigos de Estado HTTP
- `200` - OK (éxito en GET, PUT)
- `201` - Created (éxito en POST)
- `204` - No Content (éxito en DELETE)
- `401` - Unauthorized (falta token o inválido)
- `403` - Forbidden (usuario no tiene permisos)
- `404` - Not Found (recurso no existe)
- `422` - Unprocessable Entity (validación fallida)
- `500` - Internal Server Error (error del servidor)

---

## Notas de Seguridad

- Los tokens son válidos hasta que se cierren sesión explícitamente
- Las contraseñas se hashean con bcrypt
- La API implementa validación de entrada en todos los endpoints
- Se recomienda usar HTTPS en producción
- Los tokens deben mantenerse seguros en el cliente

---

## Multi-tenant (Tenancy)

La API soporta un modelo multi-tenant por negocio. Comportamiento clave:

- Selección de tenant: se puede indicar el negocio objetivo mediante el header `X-Business-Id` en requests a endpoints que operan sobre un negocio.
- Fallback: si no se proporciona `X-Business-Id`, ciertos endpoints usan el primer negocio asociado al usuario autenticado (por ejemplo, operaciones que requieren contexto cuando solo existe uno).
- Middleware: se implementó `App\Http\Middleware\EnsureTenant` que valida que el usuario pertenece al negocio solicitado y expone el objeto Business en la request (`$request->attributes->get('business')`) y en el contenedor de la app (`app('current_business')`).
- Endpoints que requieren tenant: GET/PUT/DELETE `/api/businesses/{id}` están protegidos por el middleware; listar/crear negocios no requieren `X-Business-Id`.

### Ejemplo con cURL (usar header X-Business-Id)

Obtener un negocio específico con header:

```bash
curl -X GET http://localhost:8000/api/businesses/5 \
  -H "Authorization: Bearer {token}" \
  -H "X-Business-Id: 5"
```

### Notas para testing

- Los tests automáticos usan factories y el middleware `EnsureTenant`. En tests de integración se puede indicar `X-Business-Id` con `->withHeader('X-Business-Id', $id)` o confiar en la asociación user-business creada por la factory.
- Si el usuario no pertenece al negocio indicado, el middleware retorna 403.

---

