<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReservationIndexRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Business;
use App\Models\Reservation;
use OpenApi\Attributes as OA;

class ReservationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Reservation::class, 'reservation');
    }
    #[OA\Get(path: '/businesses/{business_id}/reservations', tags: ['Reservation'], summary: 'Listar reservas del negocio', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Lista de reservas con paginación'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403'))])]
    public function index(ReservationIndexRequest $request, Business $business)
    {
        $this->authorize('viewAny', [Reservation::class, $business]);

        $cacheKey = sprintf('business:%s:reservations:page=%s:per=%s:search=%s:status=%s', $business->id, $request->getPage(), $request->getPerPage(), $request->getSearch() ?? 'none', $request->getStatus() ?? 'any');

        $payload = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($business, $request) {
            $query = $business->reservations()->with(['department', 'employee', 'client', 'creator']);

            if ($search = $request->getSearch()) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('notes', 'like', '%' . $search . '%')
                        ->orWhereHas('client', function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                            $employeeQuery->where('name', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('department', function ($departmentQuery) use ($search) {
                            $departmentQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            }

            if ($status = $request->getStatus()) {
                $query->where('status', $status);
            }

            $reservations = $query->orderBy('id', 'desc')
                ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

            return [
                'data' => $reservations->items(),
                'pagination' => [
                    'total' => $reservations->total(),
                    'per_page' => $reservations->perPage(),
                    'current_page' => $reservations->currentPage(),
                    'last_page' => $reservations->lastPage(),
                    'from' => $reservations->firstItem(),
                    'to' => $reservations->lastItem(),
                    'has_more_pages' => $reservations->hasMorePages(),
                ],
            ];
        });

        return response()->json($payload);
    }

    #[OA\Post(path: '/businesses/{business_id}/reservations', tags: ['Reservation'], summary: 'Crear una reserva', security: [['sanctum' => []]], responses: [new OA\Response(response: 201, description: 'Reserva creada'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))])]
    public function store(StoreReservationRequest $request, Business $business)
    {
        $this->authorize('create', [Reservation::class, $business]);
        $data = $request->validated();
        $data['business_id'] = $business->id;
        $data['created_by'] = $request->user()->id;

        $reservation = Reservation::create($data);

        return response()->json(['data' => $reservation->load(['department', 'employee', 'client', 'creator'])], 201);
    }

    #[OA\Get(path: '/businesses/{business_id}/reservations/{reservation_id}', tags: ['Reservation'], summary: 'Obtener una reserva', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Detalle de la reserva'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 404, description: 'No encontrado')])]
    public function show(Business $business, Reservation $reservation)
    {
        $this->ensureReservationBelongsToBusiness($business, $reservation);
        $this->authorize('view', $reservation);

        return response()->json(['data' => $reservation->load(['department', 'employee', 'client', 'creator'])]);
    }

    #[OA\Put(path: '/businesses/{business_id}/reservations/{reservation_id}', tags: ['Reservation'], summary: 'Actualizar una reserva', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Reserva actualizada'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))])]
    public function update(UpdateReservationRequest $request, Business $business, Reservation $reservation)
    {
        $this->ensureReservationBelongsToBusiness($business, $reservation);
        $this->authorize('update', $reservation);

        $reservation->update($request->validated());

        return response()->json(['data' => $reservation->fresh()->load(['department', 'employee', 'client', 'creator'])]);
    }

    #[OA\Delete(path: '/businesses/{business_id}/reservations/{reservation_id}', tags: ['Reservation'], summary: 'Eliminar una reserva', security: [['sanctum' => []]], responses: [new OA\Response(response: 204, description: 'Reserva eliminada'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403'))])]
    public function destroy(Business $business, Reservation $reservation)
    {
        $this->ensureReservationBelongsToBusiness($business, $reservation);
        $this->authorize('delete', $reservation);

        $reservation->delete();

        return response()->json(null, 204);
    }

    private function ensureReservationBelongsToBusiness(Business $business, Reservation $reservation): void
    {
        abort_unless($reservation->business_id === $business->id, 404);
    }
}
