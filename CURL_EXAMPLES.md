# Ejemplos de Uso de la API AgentoGo con cURL

Estos ejemplos pueden ejecutarse desde PowerShell o CMD.

## 1. Registro de Usuario

```powershell
$body = @{
    name = "Juan Pérez"
    email = "juan@example.com"
    password = "password123"
} | ConvertTo-Json

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/register" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body

$token = $response.Content | ConvertFrom-Json | Select-Object -ExpandProperty token
Write-Host "Token: $token"
```

## 2. Login de Usuario

```powershell
$body = @{
    email = "juan@example.com"
    password = "password123"
} | ConvertTo-Json

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/login" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body

$token = $response.Content | ConvertFrom-Json | Select-Object -ExpandProperty token
Write-Host "Token: $token"
```

## 3. Obtener Perfil del Usuario Autenticado

```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/me" `
    -Method GET `
    -Headers $headers

$response.Content | ConvertFrom-Json | ConvertTo-Json
```

## 4. Crear un Negocio

```powershell
$body = @{
    name = "Mi Negocio"
    timezone = "America/Bogota"
    primary_color = "#000000"
    secondary_color = "#ffffff"
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/businesses" `
    -Method POST `
    -ContentType "application/json" `
    -Body $body `
    -Headers $headers

$businessId = $response.Content | ConvertFrom-Json | Select-Object -ExpandProperty data | Select-Object -ExpandProperty id
Write-Host "Business ID: $businessId"
```

## 5. Listar Negocios del Usuario

```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/businesses?per_page=10&page=1" `
    -Method GET `
    -Headers $headers

$response.Content | ConvertFrom-Json | ConvertTo-Json
```

## 6. Obtener un Negocio Específico

```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/businesses/$businessId" `
    -Method GET `
    -Headers $headers

$response.Content | ConvertFrom-Json | ConvertTo-Json
```

## 7. Actualizar un Negocio

```powershell
$body = @{
    name = "Mi Negocio Actualizado"
    timezone = "America/Mexico_City"
} | ConvertTo-Json

$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/businesses/$businessId" `
    -Method PUT `
    -ContentType "application/json" `
    -Body $body `
    -Headers $headers

$response.Content | ConvertFrom-Json | ConvertTo-Json
```

## 8. Listar Registros de Auditoría

```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

# Sin filtros
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/audit-logs" `
    -Method GET `
    -Headers $headers

# Con filtros
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/audit-logs?model_type=App\\Models\\Business&action=created&per_page=20" `
    -Method GET `
    -Headers $headers

$response.Content | ConvertFrom-Json | ConvertTo-Json
```

## 9. Eliminar un Negocio

```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/businesses/$businessId" `
    -Method DELETE `
    -Headers $headers

Write-Host "Status: $($response.StatusCode)"
```

## 10. Logout

```powershell
$headers = @{
    "Authorization" = "Bearer $token"
    "Accept" = "application/json"
}

$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/auth/logout" `
    -Method POST `
    -Headers $headers

Write-Host "Status: $($response.StatusCode)"
```

## 📌 Notas Importantes

- Reemplaza `http://127.0.0.1:8000` con tu URL base si está en otro servidor
- El token se obtiene del response de `register` o `login`
- Todos los endpoints requieren token de autenticación excepto `register` y `login`
- Los errores retornan HTTP 422 con mensajes detallados de validación
- Los registros de auditoría se crean automáticamente cuando se crean/actualizan/eliminan entidades

## 🌐 Alternativamente: Usar Swagger UI

Una vez que ejecutes `php artisan l5-swagger:generate`, puedes:
1. Acceder a: http://127.0.0.1:8000/api/documentation
2. Hacer clic en "Try it out" en cualquier endpoint
3. Rellenar los parámetros
4. Ver la respuesta directamente en el navegador
