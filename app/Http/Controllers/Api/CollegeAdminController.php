<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollegeAdmin;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CollegeAdminController extends Controller
{
    /**
     * Display a listing of college admins
     */
    public function index(Request $request): JsonResponse
    {
        $query = CollegeAdmin::query();

        // Filter by active status if requested
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('college_name', 'like', '%' . $search . '%')
                    ->orWhere('department', 'like', '%' . $search . '%');
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 5);
        $collegeAdmins = $query->orderBy('name')
            ->paginate($perPage);

        return response()->json($collegeAdmins);
    }

    /**
     * Get only active college admins (for dropdowns)
     */
    public function getActiveAdmins(): JsonResponse
    {
        $admins = CollegeAdmin::active()
            ->select('id', 'name', 'position', 'college_name', 'department')
            ->orderBy('name')
            ->get();

        return response()->json($admins);
    }

    /**
     * Store a newly created college admin
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:college_admins,email',
            'phone' => 'nullable|string|max:20',
            'college_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $collegeAdmin = CollegeAdmin::create($validated);

        return response()->json([
            'message' => 'College admin created successfully',
            'data' => $collegeAdmin
        ], 201);
    }

    /**
     * Display the specified college admin
     */
    public function show(CollegeAdmin $collegeAdmin): JsonResponse
    {
        return response()->json($collegeAdmin);
    }

    /**
     * Update the specified college admin
     */
    public function update(Request $request, CollegeAdmin $collegeAdmin): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('college_admins', 'email')->ignore($collegeAdmin->id)
            ],
            'phone' => 'nullable|string|max:20',
            'college_name' => 'sometimes|required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|required|string|max:255',
            'is_active' => 'boolean'
        ]);

        $collegeAdmin->update($validated);

        return response()->json([
            'message' => 'College admin updated successfully',
            'data' => $collegeAdmin
        ]);
    }

    /**
     * Remove the specified college admin
     */
    public function destroy(CollegeAdmin $collegeAdmin): JsonResponse
    {
        // Check if admin has students
        if ($collegeAdmin->students()->exists()) {
            return response()->json([
                'message' => 'Cannot delete college admin with existing students'
            ], 422);
        }

        $collegeAdmin->delete();

        return response()->json([
            'message' => 'College admin deleted successfully'
        ]);
    }

    /**
     * Get statistics for a college admin
     */
    public function statistics(CollegeAdmin $collegeAdmin): JsonResponse
    {
        $stats = [
            'total_students' => $collegeAdmin->students()->count(),
            'active_students' => $collegeAdmin->students()->where('status', 'active')->count(),
            'at_risk_students' => $collegeAdmin->students()->where('status', 'at_risk')->count(),
            'dropped_out_students' => $collegeAdmin->students()->where('status', 'dropped_out')->count(),
            'graduated_students' => $collegeAdmin->students()->where('status', 'graduated')->count(),
        ];

        return response()->json([
            'admin' => $collegeAdmin,
            'statistics' => $stats
        ]);
    }
}
