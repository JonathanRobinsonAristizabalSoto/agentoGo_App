<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="AgentoGo API",
 *     version="0.3.0",
 *     description="API REST multi-tenant con autenticación, auditoría y gestión de negocios",
 *     contact={
 *         "name": "AgentoGo Support",
 *         "email": "support@agentogo.com"
 *     }
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor de desarrollo"
 * )
 * 
 * @OA\SecurityScheme(
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="token",
 *     securityScheme="sanctum",
 *     description="Token de autenticación Sanctum"
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Juan Pérez"),
 *         @OA\Property(property="email", type="string", example="juan@example.com"),
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="Business",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Mi Negocio"),
 *         @OA\Property(property="slug", type="string", example="mi-negocio"),
 *         @OA\Property(property="status", type="string", enum={"active", "inactive"}, example="active"),
 *         @OA\Property(property="created_at", type="string", format="date-time"),
 *         @OA\Property(property="updated_at", type="string", format="date-time")
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="AuditLog",
 *     type="object",
 *     properties={
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="user_id", type="integer"),
 *         @OA\Property(property="model_type", type="string", example="App\\Models\\Business"),
 *         @OA\Property(property="model_id", type="integer"),
 *         @OA\Property(property="action", type="string", enum={"created", "updated", "deleted"}),
 *         @OA\Property(property="old_values", type="object"),
 *         @OA\Property(property="new_values", type="object"),
 *         @OA\Property(property="ip_address", type="string"),
 *         @OA\Property(property="user_agent", type="string"),
 *         @OA\Property(property="created_at", type="string", format="date-time")
 *     }
 * )
 * 
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     properties={
 *         @OA\Property(property="total", type="integer", example=25),
 *         @OA\Property(property="per_page", type="integer", example=15),
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=2),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="to", type="integer", example=15),
 *         @OA\Property(property="has_more_pages", type="boolean", example=true)
 *     }
 * )
 */
abstract class Controller
{
    use AuthorizesRequests;
}
