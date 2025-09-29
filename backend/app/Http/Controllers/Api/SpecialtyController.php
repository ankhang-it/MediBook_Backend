<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SpecialtyController extends Controller
{
    /**
     * Get all specialties.
     */
    public function getSpecialties(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            $query = Specialty::query();

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $specialties = $query->withCount('doctors')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $specialties
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve specialties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specialty by ID.
     */
    public function getSpecialty($id)
    {
        try {
            $specialty = Specialty::withCount('doctors')->find($id);
            
            if (!$specialty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialty not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $specialty
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve specialty',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new specialty.
     */
    public function createSpecialty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:specialties',
            'description' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $specialty = Specialty::create([
                'specialty_id' => \Illuminate\Support\Str::uuid(),
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Specialty created successfully',
                'data' => $specialty
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update specialty.
     */
    public function updateSpecialty(Request $request, $id)
    {
        $specialty = Specialty::find($id);
        
        if (!$specialty) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:specialties,name,' . $id . ',specialty_id',
            'description' => 'sometimes|required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $specialty->update([
                'name' => $request->get('name', $specialty->name),
                'description' => $request->get('description', $specialty->description),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Specialty updated successfully',
                'data' => $specialty
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete specialty.
     */
    public function deleteSpecialty($id)
    {
        $specialty = Specialty::find($id);
        
        if (!$specialty) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty not found'
            ], 404);
        }

        // Check if specialty has doctors
        if ($specialty->doctors()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete specialty that has doctors assigned'
            ], 400);
        }

        try {
            $specialty->delete();

            return response()->json([
                'success' => true,
                'message' => 'Specialty deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Specialty deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctors by specialty.
     */
    public function getDoctorsBySpecialty($id, Request $request)
    {
        try {
            $specialty = Specialty::find($id);
            
            if (!$specialty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Specialty not found'
                ], 404);
            }

            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            $query = $specialty->doctors()->with(['user', 'specialty']);

            if ($search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('fullname', 'like', "%{$search}%");
            }

            $doctors = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $doctors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve doctors',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
