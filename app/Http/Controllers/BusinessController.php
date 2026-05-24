<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Http\Requests\BusinessIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

/**
 * Controlador de negocios: listar, crear negocios y generar slugs únicos.
 */
class BusinessController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Business::class, 'business');
    }
    #[OA\Get(
        path: '/businesses',
        tags: ['Business'],
        summary: 'Listar negocios del usuario',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Lista de negocios con paginación')]
    )]
    /**
     * @OA\Get(
     *     path="/businesses",
     *     tags={"Business"},
     *     summary="Listar negocios del usuario",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="per_page", in="query", description="Items por página", schema={"type": "integer", "default": 15, "maximum": 100}),
     *     @OA\Parameter(name="page", in="query", description="Número de página", schema={"type": "integer", "default": 1}),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de negocios con paginación",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="data", type="array", items={"$ref": "#/components/schemas/Business"}),
     *                     @OA\Property(property="pagination", ref="#/components/schemas/PaginationMeta")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(BusinessIndexRequest $request)
    {
        $perPage = $request->getPerPage();
        $page = $request->getPage();
        $search = $request->getSearch();
        $status = $request->getStatus();
        
        $businesses = $request->user()
            ->businesses()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('businesses.name', 'like', '%' . $search . '%')
                        ->orWhere('businesses.slug', 'like', '%' . $search . '%');
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->where('businesses.status', $status);
            })
            ->orderBy('businesses.id', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $businesses->items(),
            'pagination' => [
                'total' => $businesses->total(),
                'per_page' => $businesses->perPage(),
                'current_page' => $businesses->currentPage(),
                'last_page' => $businesses->lastPage(),
                'from' => $businesses->firstItem(),
                'to' => $businesses->lastItem(),
                'has_more_pages' => $businesses->hasMorePages(),
            ],
        ]);
    }

    #[OA\Post(
        path: '/businesses',
        tags: ['Business'],
        summary: 'Crear un nuevo negocio',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 201, description: 'Negocio creado exitosamente'), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError', example: ['message' => 'The given data was invalid.', 'errors' => ['name' => ['El nombre del negocio es obligatorio.']]]))]
    )]
    /**
     * @OA\Post(
     *     path="/businesses",
     *     tags={"Business"},
     *     summary="Crear un nuevo negocio",
     *     security={{"sanctum": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="name", type="string", example="Mi Negocio"),
     *                     @OA\Property(property="slug", type="string", nullable=true, example="mi-negocio"),
     *                     @OA\Property(property="timezone", type="string", nullable=true, example="America/Bogota"),
     *                     @OA\Property(property="primary_color", type="string", nullable=true, example="#000000"),
     *                     @OA\Property(property="secondary_color", type="string", nullable=true, example="#ffffff")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Negocio creado exitosamente",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="data", ref="#/components/schemas/Business")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function store(StoreBusinessRequest $request)
    {
        $data = $request->validated();

        $slugBase = $data['slug'] ?? Str::slug($data['name']);
        $slug = $this->generateUniqueSlug($slugBase);

        $business = Business::create([
            'name' => $data['name'],
            'slug' => $slug,
            'timezone' => $data['timezone'] ?? null,
            'primary_color' => $data['primary_color'] ?? null,
            'secondary_color' => $data['secondary_color'] ?? null,
            'status' => 'active',
        ]);

        $request->user()->businesses()->attach($business->id, [
            'role' => 'owner',
        ]);

        return response()->json([
            'data' => $business->fresh(),
        ], 201);
    }

    #[OA\Get(
        path: '/businesses/{business_id}',
        tags: ['Business'],
        summary: 'Obtener detalles de un negocio',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Detalles del negocio'), new OA\Response(response: 403, description: 'Acceso denegado', content: new OA\JsonContent(ref: '#/components/schemas/Error403', example: ['code' => 403, 'message' => 'No tiene permisos para realizar esta acción.']))]
    )]
    /**
     * @OA\Get(
     *     path="/businesses/{business_id}",
     *     tags={"Business"},
     *     summary="Obtener detalles de un negocio",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="business_id", in="path", required=true, schema={"type": "integer"}),
     *     @OA\Response(
     *         response=200,
     *         description="Detalles del negocio",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="data", ref="#/components/schemas/Business")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado"),
     *     @OA\Response(response=404, description="Negocio no encontrado")
     * )
     */
    public function show(Request $request, Business $business)
    {
        $this->authorize('view', $business);

        return response()->json([
            'data' => $business,
        ]);
    }

    #[OA\Put(
        path: '/businesses/{business_id}',
        tags: ['Business'],
        summary: 'Actualizar un negocio',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Negocio actualizado'), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError', example: ['message' => 'The given data was invalid.', 'errors' => ['name' => ['El nombre del negocio es obligatorio.']]])), new OA\Response(response: 403, description: 'Acceso denegado', content: new OA\JsonContent(ref: '#/components/schemas/Error403', example: ['code' => 403, 'message' => 'No tiene permisos para realizar esta acción.']))]
    )]
    /**
     * @OA\Put(
     *     path="/businesses/{business_id}",
     *     tags={"Business"},
     *     summary="Actualizar un negocio",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="business_id", in="path", required=true, schema={"type": "integer"}),
     *     @OA\RequestBody(
     *         required=true,
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="timezone", type="string", nullable=true),
     *                     @OA\Property(property="primary_color", type="string", nullable=true),
     *                     @OA\Property(property="secondary_color", type="string", nullable=true)
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Negocio actualizado",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 schema={
     *                     @OA\Property(property="data", ref="#/components/schemas/Business")
     *                 }
     *             )
     *         }
     *     ),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado"),
     *     @OA\Response(response=404, description="Negocio no encontrado"),
     *     @OA\Response(response=422, description="Validación fallida")
     * )
     */
    public function update(UpdateBusinessRequest $request, Business $business)
    {
        $this->authorize('update', $business);

        $data = $request->validated();

        $business->update($data);

        return response()->json([
            'data' => $business->fresh(),
        ]);
    }

    #[OA\Delete(
        path: '/businesses/{business_id}',
        tags: ['Business'],
        summary: 'Eliminar un negocio',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 204, description: 'Negocio eliminado'), new OA\Response(response: 403, description: 'Acceso denegado', content: new OA\JsonContent(ref: '#/components/schemas/Error403', example: ['code' => 403, 'message' => 'No tiene permisos para realizar esta acción.']))]
    )]
    /**
     * @OA\Delete(
     *     path="/businesses/{business_id}",
     *     tags={"Business"},
     *     summary="Eliminar un negocio",
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(name="business_id", in="path", required=true, schema={"type": "integer"}),
     *     @OA\Response(response=204, description="Negocio eliminado"),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=403, description="Acceso denegado"),
     *     @OA\Response(response=404, description="Negocio no encontrado")
     * )
     */
    public function destroy(Request $request, Business $business)
    {
        $this->authorize('delete', $business);

        $business->delete();

        return response()->json(null, 204);
    }

    private function generateUniqueSlug(string $slugBase): string
    {
        $slugBase = trim($slugBase) !== '' ? $slugBase : 'business';
        $slug = $slugBase;
        $counter = 2;

        while (Business::where('slug', $slug)->exists()) {
            $slug = $slugBase.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
