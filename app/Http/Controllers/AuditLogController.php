<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditLogFilterRequest;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AuditLogController extends Controller
{
    #[OA\Get(
        path: '/audit-logs',
        tags: ['AuditLog'],
        summary: 'Listar registros de auditoría',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista de registros de auditoría con paginación')]
    )]
    /**
     * @OA\Get(
     *     path="/audit-logs",
     *     tags={"AuditLog"},
     *     summary="Listar registros de auditoría",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="per_page", in="query", description="Items por página", schema={"type": "integer", "default": 15, "maximum": 100}),
     *     @OA\Parameter(name="page", in="query", description="Número de página", schema={"type": "integer", "default": 1}),
     *     @OA\Parameter(name="model_type", in="query", description="Tipo de modelo (ej: App\\Models\\Business)", schema={"type": "string"}),
     *     @OA\Parameter(name="model_id", in="query", description="ID del modelo", schema={"type": "integer"}),
     *     @OA\Parameter(name="action", in="query", description="Acción (created, updated, deleted)", schema={"type": "string", "enum": {"created", "updated", "deleted"}}),
     *     @OA\Parameter(name="user_id", in="query", description="ID del usuario que realizó la acción", schema={"type": "integer"}),
     *     @OA\Parameter(name="date_from", in="query", description="Fecha desde (Y-m-d)", schema={"type": "string", "format": "date"}),
     *     @OA\Parameter(name="date_to", in="query", description="Fecha hasta (Y-m-d)", schema={"type": "string", "format": "date"}),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de registros de auditoría con paginación",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="data", type="array", items={"$ref": "#/components/schemas/AuditLog"}),
     *                     @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function index(AuditLogFilterRequest $request): JsonResponse
    {
        $query = AuditLog::query();

        // Filtro por model_type (ej: App\Models\Business)
        if ($request->has('model_type')) {
            $query->where('model_type', $request->get('model_type'));
        }

        // Filtro por model_id
        if ($request->has('model_id')) {
            $query->where('model_id', $request->get('model_id'));
        }

        // Filtro por action (created, updated, deleted)
        if ($request->has('action')) {
            $query->where('action', $request->get('action'));
        }

        // Filtro por user_id
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }

        // Filtro por rango de fechas
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        // Ordenar por fecha descendente (más recientes primero)
        $query->orderBy('created_at', 'desc');

        // Paginar
        $audits = $query->paginate($request->getPerPage());

        return response()->json([
            'data' => $audits->items(),
            'pagination' => [
                'total' => $audits->total(),
                'per_page' => $audits->perPage(),
                'current_page' => $audits->currentPage(),
                'last_page' => $audits->lastPage(),
                'from' => $audits->firstItem(),
                'to' => $audits->lastItem(),
                'has_more_pages' => $audits->hasMorePages(),
            ],
        ]);
    }
}
