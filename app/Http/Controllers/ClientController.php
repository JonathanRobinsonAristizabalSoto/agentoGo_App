<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientIndexRequest;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Business;
use App\Models\Client;
use OpenApi\Attributes as OA;

class ClientController extends Controller
{
    #[OA\Get(path: '/businesses/{business_id}/clients', tags: ['Client'], summary: 'Listar clientes del negocio', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Lista de clientes con paginación'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403'))])]
    public function index(ClientIndexRequest $request, Business $business)
    {
        $this->authorize('viewAny', [Client::class, $business]);
        $query = $business->clients();

        if ($search = $request->getSearch()) {
            $query->where(function ($nestedQuery) use ($search) {
                $nestedQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%');
            });
        }

        if ($status = $request->getStatus()) {
            $query->where('status', $status);
        }

        $clients = $query->orderBy('id', 'desc')
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return response()->json([
            'data' => $clients->items(),
            'pagination' => [
                'total' => $clients->total(),
                'per_page' => $clients->perPage(),
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'from' => $clients->firstItem(),
                'to' => $clients->lastItem(),
                'has_more_pages' => $clients->hasMorePages(),
            ],
        ]);
    }

    #[OA\Post(path: '/businesses/{business_id}/clients', tags: ['Client'], summary: 'Crear un cliente', security: [['sanctum' => []]], responses: [new OA\Response(response: 201, description: 'Cliente creado'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))])]
    public function store(StoreClientRequest $request, Business $business)
    {
        $this->authorize('create', [Client::class, $business]);
        $data = $request->validated();
        $data['business_id'] = $business->id;

        $client = Client::create($data);

        return response()->json(['data' => $client], 201);
    }

    #[OA\Get(path: '/businesses/{business_id}/clients/{client_id}', tags: ['Client'], summary: 'Obtener un cliente', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Detalle del cliente'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 404, description: 'No encontrado')])]
    public function show(Business $business, Client $client)
    {
        $this->ensureClientBelongsToBusiness($business, $client);
        $this->authorize('view', $client);

        return response()->json(['data' => $client]);
    }

    #[OA\Put(path: '/businesses/{business_id}/clients/{client_id}', tags: ['Client'], summary: 'Actualizar un cliente', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Cliente actualizado'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))])]
    public function update(UpdateClientRequest $request, Business $business, Client $client)
    {
        $this->ensureClientBelongsToBusiness($business, $client);
        $this->authorize('update', $client);

        $client->update($request->validated());

        return response()->json(['data' => $client->fresh()]);
    }

    #[OA\Delete(path: '/businesses/{business_id}/clients/{client_id}', tags: ['Client'], summary: 'Eliminar un cliente', security: [['sanctum' => []]], responses: [new OA\Response(response: 204, description: 'Cliente eliminado'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403'))])]
    public function destroy(Business $business, Client $client)
    {
        $this->ensureClientBelongsToBusiness($business, $client);
        $this->authorize('delete', $client);

        $client->delete();

        return response()->json(null, 204);
    }

    private function ensureClientBelongsToBusiness(Business $business, Client $client): void
    {
        abort_unless($client->business_id === $business->id, 404);
    }
}
