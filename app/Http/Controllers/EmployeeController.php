<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeIndexRequest;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Business;
use App\Models\Employee;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class EmployeeController extends Controller
{
    #[OA\Get(path: '/businesses/{business_id}/employees', tags: ['Employee'], summary: 'Listar empleados del negocio', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Lista de empleados con paginación'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403'))])]
    public function index(EmployeeIndexRequest $request, Business $business)
    {
        $this->authorize('viewAny', [Employee::class, $business]);

        $cacheKey = sprintf('business:%s:employees:page=%s:per=%s:search=%s:status=%s', $business->id, $request->getPage(), $request->getPerPage(), $request->getSearch() ?? 'none', $request->getStatus() ?? 'any');

        $payload = \Illuminate\Support\Facades\Cache::remember($cacheKey, 60, function () use ($business, $request) {
            $query = $business->employees()->with('department');

            if ($search = $request->getSearch()) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('position', 'like', '%' . $search . '%');
                });
            }

            if ($status = $request->getStatus()) {
                $query->where('status', $status);
            }

            $employees = $query->orderBy('id', 'desc')
                ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

            return [
                'data' => $employees->items(),
                'pagination' => [
                    'total' => $employees->total(),
                    'per_page' => $employees->perPage(),
                    'current_page' => $employees->currentPage(),
                    'last_page' => $employees->lastPage(),
                    'from' => $employees->firstItem(),
                    'to' => $employees->lastItem(),
                    'has_more_pages' => $employees->hasMorePages(),
                ],
            ];
        });

        return response()->json($payload);
    }

    #[OA\Post(path: '/businesses/{business_id}/employees', tags: ['Employee'], summary: 'Crear un empleado', security: [['sanctum' => []]], responses: [new OA\Response(response: 201, description: 'Empleado creado'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))])]
    public function store(StoreEmployeeRequest $request, Business $business)
    {
        $this->authorize('create', [Employee::class, $business]);
        $data = $request->validated();
        $data['business_id'] = $business->id;

        $employee = Employee::create($data);

        return response()->json(['data' => $employee], 201);
    }

    #[OA\Get(path: '/businesses/{business_id}/employees/{employee_id}', tags: ['Employee'], summary: 'Obtener un empleado', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Detalle del empleado'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 404, description: 'No encontrado')])]
    public function show(Business $business, Employee $employee)
    {
        $this->ensureEmployeeBelongsToBusiness($business, $employee);
        $this->authorize('view', $employee);

        return response()->json(['data' => $employee->load('department')]);
    }

    #[OA\Put(path: '/businesses/{business_id}/employees/{employee_id}', tags: ['Employee'], summary: 'Actualizar un empleado', security: [['sanctum' => []]], responses: [new OA\Response(response: 200, description: 'Empleado actualizado'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403')), new OA\Response(response: 422, description: 'Validación fallida', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError'))])]
    public function update(UpdateEmployeeRequest $request, Business $business, Employee $employee)
    {
        $this->ensureEmployeeBelongsToBusiness($business, $employee);
        $this->authorize('update', $employee);

        $employee->update($request->validated());

        return response()->json(['data' => $employee->fresh()->load('department')]);
    }

    #[OA\Delete(path: '/businesses/{business_id}/employees/{employee_id}', tags: ['Employee'], summary: 'Eliminar un empleado', security: [['sanctum' => []]], responses: [new OA\Response(response: 204, description: 'Empleado eliminado'), new OA\Response(response: 403, description: 'No autorizado', content: new OA\JsonContent(ref: '#/components/schemas/Error403'))])]
    public function destroy(Business $business, Employee $employee)
    {
        $this->ensureEmployeeBelongsToBusiness($business, $employee);
        $this->authorize('delete', $employee);

        $employee->delete();

        return response()->json(null, 204);
    }

    private function ensureEmployeeBelongsToBusiness(Business $business, Employee $employee): void
    {
        abort_unless($employee->business_id === $business->id, 404);
    }
}
