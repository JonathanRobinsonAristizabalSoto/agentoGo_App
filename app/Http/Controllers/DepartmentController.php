<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentIndexRequest;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Business;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index(DepartmentIndexRequest $request, Business $business)
    {
        $query = $business->departments();

        if ($search = $request->getSearch()) {
            $query->where(function ($nestedQuery) use ($search) {
                $nestedQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        if ($status = $request->getStatus()) {
            $query->where('status', $status);
        }

        $departments = $query->orderBy('id', 'desc')
            ->paginate($request->getPerPage(), ['*'], 'page', $request->getPage());

        return response()->json([
            'data' => $departments->items(),
            'pagination' => [
                'total' => $departments->total(),
                'per_page' => $departments->perPage(),
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'from' => $departments->firstItem(),
                'to' => $departments->lastItem(),
                'has_more_pages' => $departments->hasMorePages(),
            ],
        ]);
    }

    public function store(StoreDepartmentRequest $request, Business $business)
    {
        $data = $request->validated();
        $slugBase = $data['slug'] ?? Str::slug($data['name']);

        $department = Department::create([
            'business_id' => $business->id,
            'name' => $data['name'],
            'slug' => $this->generateUniqueSlug($business, $slugBase),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        return response()->json(['data' => $department], 201);
    }

    public function show(Business $business, Department $department)
    {
        $this->ensureDepartmentBelongsToBusiness($business, $department);

        return response()->json(['data' => $department]);
    }

    public function update(UpdateDepartmentRequest $request, Business $business, Department $department)
    {
        $this->ensureDepartmentBelongsToBusiness($business, $department);

        $data = $request->validated();

        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $slugBase = $data['slug'] ?? Str::slug($data['name'] ?? $department->name);
            $data['slug'] = $this->generateUniqueSlug($business, $slugBase, $department->id);
        }

        $department->update($data);

        return response()->json(['data' => $department->fresh()]);
    }

    public function destroy(Business $business, Department $department)
    {
        $this->ensureDepartmentBelongsToBusiness($business, $department);

        $department->delete();

        return response()->json(null, 204);
    }

    private function ensureDepartmentBelongsToBusiness(Business $business, Department $department): void
    {
        abort_unless($department->business_id === $business->id, 404);
    }

    private function generateUniqueSlug(Business $business, string $slugBase, ?int $ignoreId = null): string
    {
        $slugBase = trim($slugBase) !== '' ? $slugBase : 'department';
        $slug = $slugBase;
        $counter = 2;

        while ($business->departments()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}