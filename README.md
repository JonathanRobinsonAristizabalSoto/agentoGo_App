<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# API AgentoGo - Backend (Laravel 12)

API REST construida con Laravel 12 que proporciona autenticación con tokens (Sanctum), gestión multi-tenant de negocios, y endpoints protegidos.

## Cambios Recientes (v0.3-dev)

- ✅ **FormRequests centralizados**: `StoreBusinessRequest`, `UpdateBusinessRequest`, `PaginationRequest`, `BusinessIndexRequest`
- ✅ **Listado de negocios mejorado**: búsqueda por nombre/slug y filtro por estado en GET /businesses
- ✅ **Módulos base del negocio**: departamentos, empleados, clientes y reservas
- ✅ **Autorización basada en roles**: `BusinessPolicy` (owner, editor, viewer)
- ✅ **Paginación**: GET /businesses con `per_page` (default: 15) y `page`
- ✅ **Multi-tenant**: Middleware `EnsureTenant` + roles en pivot `business_user`
- ✅ **Validación**: Mensajes de error en español, reglas centralizadas
- ✅ **Auditoría automática**: AuditLog model + trait Auditable para rastrear cambios
- ✅ **Endpoint GET /audit-logs**: Listar logs con filtros (model_type, action, user_id) y paginación
- ✅ **OpenAPI/Swagger**: Documentación interactiva con L5-Swagger (esquemas y ejemplos 403/422 añadidos)
- ✅ **Tests**: 68 tests passing (314 assertions)
- ✅ **Seguridad básica**: `SecurityHeaders` middleware añadido y `RateLimiter` configurado

## Inicio Rápido

```powershell
cd C:\Users\Aristizabal\OneDrive\Desktop\AgentoGo\apps\api

# Instalación (primera vez)
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate

# Ejecutar API
php artisan serve

# Ejecutar tests
php artisan test --testdox
```

**Endpoints base:** http://127.0.0.1:8000/api

## Arquitectura Actual

- **Autenticación:** Laravel Sanctum (bearer tokens)
- **Multi-tenant:** Middleware `EnsureTenant` + pivot `business_user` con roles
- **Autorización:** `BusinessPolicy` con control de acceso basado en roles
- **Validación:** FormRequests centralizados con mensajes en español
- **Paginación:** GET /businesses con parámetros `per_page` y `page`
- **Búsqueda/Filtros:** GET /businesses con `search` y `status`
- **Auditoría:** Trait `Auditable` + `AuditLog` model para rastrear create/update/delete
- **Modelos:** User, Business, AuditLog (relaciones configuradas)
- **Migraciones:** users, businesses, business_user, personal_access_tokens, audit_logs
- **Controllers:** AuthController, BusinessController, AuditLogController
- **Tests:** 49 passing (Auth, Business, MultiTenant, Pagination, Auditing, BusinessModules)

## Documentación

Ver [API_DOCUMENTATION.md](./API_DOCUMENTATION.md) para:
- Endpoints de autenticación (register, login, logout, /me)
- Endpoints de negocios (CRUD con paginación)
- Headers requeridos (Authorization, X-Business-Id)
- Ejemplos cURL completos
- Códigos de estado y errores

### Resumen de Endpoints

| Método | Endpoint | Descripción | Auth | Status |
|--------|----------|-------------|------|--------|
| POST | `/auth/register` | Registro de usuario | ❌ | ✅ |
| POST | `/auth/login` | Login de usuario | ❌ | ✅ |
| POST | `/auth/logout` | Logout de usuario | ✅ | ✅ |
| GET | `/auth/me` | Obtener perfil | ✅ | ✅ |
| GET | `/businesses` | Listar negocios (paginado) | ✅ | ✅ |
| POST | `/businesses` | Crear negocio | ✅ | ✅ |
| GET | `/businesses/{id}` | Obtener negocio | ✅ | ✅ |
| PUT | `/businesses/{id}` | Actualizar negocio | ✅ | ✅ |
| DELETE | `/businesses/{id}` | Eliminar negocio | ✅ | ✅ |
| GET | `/audit-logs` | Listar logs (filtros + paginación) | ✅ | ✅ |

## Próximos Pasos (Recomendado)

1. **Relacionamientos:** Departamentos, empleados, clientes por negocio
2. **Webhooks:** Notificaciones de cambios en tiempo real
3. **Rate limiting:** Protección contra abuso
4. **Type safety:** PHPStan level 8 o Psalm
5. **CI/CD:** Tests automáticos (GitHub Actions)

## Comandos Útiles

```bash
# Tests
php artisan test                    # Ejecutar todos
php artisan test --testdox          # Con formato testdox
php artisan test tests/Feature/*    # Específicos

# Migraciones
php artisan migrate                 # Aplicar migraciones
php artisan migrate:refresh         # Reset + migrate (testing)
php artisan tinker                  # REPL para debug

# Lint
composer format   # Si está configurado
```

## Estructura de Directorios

```
apps/api/
├── app/
│   ├── Models/              # User, Business, Department, Employee, Client, Reservation, AuditLog
│   ├── Http/
│   │   ├── Controllers/     # AuthController, BusinessController, AuditLogController
│   │   ├── Middleware/      # EnsureTenant
│   │   └── Requests/        # StoreBusinessRequest, UpdateBusinessRequest, PaginationRequest, BusinessIndexRequest, AuditLogFilterRequest
│   ├── Policies/            # BusinessPolicy (autorización basada en roles)
│   ├── Providers/           # AppServiceProvider (registro de policies)
│   └── Auditable.php        # Trait para auditoría automática
├── database/
│   ├── migrations/
│   ├── factories/           # UserFactory, BusinessFactory
│   └── seeders/
├── routes/
│   └── api.php              # Rutas de API
├── tests/
│   └── Feature/             # 49 tests (Auth, Business, Pagination, MultiTenant, Audit, AuditLog, Swagger, Modules)
├── API_DOCUMENTATION.md     # Documentación completa de endpoints
└── README.md                # Este archivo
```

## Documentación Interactiva (OpenAPI/Swagger)

La API incluye documentación interactiva generada automáticamente con L5-Swagger.

**Configurar Swagger:**

```powershell
# Publicar configuración de L5-Swagger
php artisan vendor:publish --provider=L5\Swagger\SwaggerServiceProvider

# Generar documentación Swagger
php artisan l5-swagger:generate
```

**Acceder a la documentación:**
- URL: http://127.0.0.1:8000/api/documentation
- Prueba endpoints directamente desde el navegador
- Genera automáticamente requests CURL
- Soporta autenticación Bearer Token

**Endpoints documentados:**
1. `POST /auth/register` - Registrar usuario
2. `POST /auth/login` - Iniciar sesión
3. `GET /auth/me` - Perfil actual (auth required)
4. `POST /auth/logout` - Cerrar sesión (auth required)
5. `GET /businesses` - Listar negocios del usuario (auth required)
6. `POST /businesses` - Crear negocio (auth required)
7. `GET /businesses/{id}` - Obtener negocio (auth required)
8. `PUT /businesses/{id}` - Actualizar negocio (auth required)
9. `DELETE /businesses/{id}` - Eliminar negocio (auth required)
10. `GET /audit-logs` - Listar logs de auditoría (auth required)

## Notas de Seguridad

- Tokens Sanctum válidos hasta logout explícito
- Contraseñas hasheadas con bcrypt
- Middleware valida pertenencia a tenant antes de acceder
- Usar HTTPS en producción

## Contacto

Para reportes o preguntas sobre la API, revisar plan.md o comentarios en el código.

