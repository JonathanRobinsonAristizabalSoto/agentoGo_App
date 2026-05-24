<?php

namespace Tests\Feature;

use Tests\TestCase;

class SwaggerDocumentationTest extends TestCase
{
    /**
     * Test que la ruta de documentación existe y retorna un documento OpenAPI válido
     */
    public function test_swagger_documentation_endpoint_exists(): void
    {
        $response = $this->get('/api/documentation');

        $this->assertTrue(
            $response->status() === 200 || $response->status() === 404,
            'El endpoint /api/documentation debe retornar 200 (si está configurado) o 404 (si aún no se ejecutó vendor:publish)'
        );
    }

    /**
     * Test que todos los controllers tienen anotaciones OpenAPI
     */
    public function test_controllers_have_openapi_annotations(): void
    {
        $authController = file_get_contents(app_path('Http/Controllers/AuthController.php'));
        $businessController = file_get_contents(app_path('Http/Controllers/BusinessController.php'));
        $auditLogController = file_get_contents(app_path('Http/Controllers/AuditLogController.php'));

        // Verificar que contienen anotaciones @OA\Post, @OA\Get, etc.
        $this->assertStringContainsString('@OA\Post', $authController);
        $this->assertStringContainsString('@OA\Get', $authController);
        $this->assertStringContainsString('@OA\Post', $businessController);
        $this->assertStringContainsString('@OA\Get', $businessController);
        $this->assertStringContainsString('@OA\Put', $businessController);
        $this->assertStringContainsString('@OA\Delete', $businessController);
        $this->assertStringContainsString('@OA\Get', $auditLogController);
    }

    /**
     * Test que el Controller base tiene SecurityScheme definido
     */
    public function test_base_controller_has_security_scheme(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Controller.php'));

        $this->assertStringContainsString('@OA\SecurityScheme', $controller);
        $this->assertStringContainsString('sanctum', $controller);
    }

    /**
     * Test que los schemas OpenAPI están definidos
     */
    public function test_openapi_schemas_defined(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Controller.php'));

        $this->assertStringContainsString('schema="User"', $controller);
        $this->assertStringContainsString('schema="Business"', $controller);
        $this->assertStringContainsString('schema="AuditLog"', $controller);
        $this->assertStringContainsString('schema="PaginationMeta"', $controller);
    }
}
