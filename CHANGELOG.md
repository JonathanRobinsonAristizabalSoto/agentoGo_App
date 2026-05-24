# Changelog - AgentoGo API

## [v0.1-dev] - 2026-05-22

### ✅ Implementado

#### Autenticación Multi-tenant
- [x] Middleware `EnsureTenant` - valida pertenencia a negocio via header `X-Business-Id`
- [x] Relación pivot `business_user` con columna `role` (owner, editor, viewer)
- [x] Route model binding para `/api/businesses/{business}`
- [x] Fallback a primer negocio del usuario si no se especifica header

#### Tests
- [x] 23 tests passing (Auth, Business, MultiTenant)
- [x] Factory `BusinessFactory::withOwner()` para tests
- [x] Tests de acceso negado (403) cuando usuario no pertenece a negocio

#### Documentación
- [x] API_DOCUMENTATION.md actualizado con sección multi-tenant
- [x] README.md del backend con guía de inicio rápido
- [x] Ejemplos de uso con cURL y headers

### 🔄 En Progreso

- [ ] BusinessPolicy y autorización by role
- [ ] FormRequests para validación centralizada
- [ ] Paginación en GET /businesses
- [ ] Logging de cambios en negocios

### 📋 Próximos Pasos (Recomendado)

1. **BusinessPolicy** - Crear `app/Policies/BusinessPolicy.php`
   - Método `view()` - solo owner y editor
   - Método `update()` - solo owner
   - Método `delete()` - solo owner

2. **FormRequests** - Centralizar validación
   - `StoreBusinessRequest`
   - `UpdateBusinessRequest`

3. **Roles y Permisos** - Sistema más granular
   - Gates (can('update_business', $business))
   - Roles in team (owner, manager, editor, viewer)

4. **Paginación** - GET /businesses con ?page=1&per_page=10

5. **Auditoría** - Log de cambios (created, updated, deleted)

6. **API Spec** - OpenAPI/Swagger

### 🐛 Bugs Conocidos

- Ninguno actualmente

### 📦 Dependencias

- Laravel 12.x
- PHP 8.2+
- Laravel Sanctum (autenticación)
- Composer

### Notas

- No se ha inicializado repositorio git aún
- Todos los tests usan RefreshDatabase (SQLite testing)
- El middleware se aplica solo a rutas que requieren negocio específico
