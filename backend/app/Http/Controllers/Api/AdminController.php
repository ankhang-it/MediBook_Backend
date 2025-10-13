<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Appointment;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats()
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        try {
            $stats = [
                'total_users' => User::count(),
                'total_doctors' => User::where('role', 'doctor')->count(),
                'total_patients' => User::where('role', 'patient')->count(),
                'total_appointments' => Appointment::count(),
                'pending_appointments' => Appointment::where('status', 'pending')->count(),
                'completed_appointments' => Appointment::where('status', 'completed')->count(),
                'total_payments' => Payment::count(),
                'total_revenue' => Payment::where('status', 'completed')->sum('total_amount'),
                'recent_appointments' => Appointment::with(['patient', 'doctor.user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users with pagination.
     */
    public function getUsers(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $role = $request->get('role', '');

            $query = User::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            if ($role) {
                $query->where('role', $role);
            }

            $users = $query->with(['doctorProfile', 'patientProfile'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all doctors with their profiles (Public version).
     */
    public function getDoctorsPublic(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $specialty = $request->get('specialty', '');

            $query = DoctorProfile::with(['user', 'specialty', 'feedback']);

            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('fullname', 'like', "%{$search}%");
            }

            if ($specialty) {
                $query->where('specialty_id', $specialty);
            }

            $doctors = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Add rating information to each doctor
            $doctors->getCollection()->transform(function ($doctor) {
                $doctor->average_rating = $doctor->average_rating;
                $doctor->total_reviews = $doctor->total_reviews;
                $doctor->rating_breakdown = $doctor->rating_breakdown;
                return $doctor;
            });

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

    /**
     * Get all doctors with their profiles (Admin version).
     */
    public function getDoctors(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');
            $specialty = $request->get('specialty', '');

            $query = DoctorProfile::with(['user', 'specialty']);

            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('fullname', 'like', "%{$search}%");
            }

            if ($specialty) {
                $query->where('specialty_id', $specialty);
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

    /**
     * Get all patients with their profiles.
     */
    public function getPatients(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search', '');

            $query = PatientProfile::with('user');

            if ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('fullname', 'like', "%{$search}%");
            }

            $patients = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $patients
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve patients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user.
     */
    public function createUser(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:patient,doctor,admin',
            'fullname' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'medical_history' => 'nullable|string',
            'specialty_id' => 'required_if:role,doctor|nullable|exists:specialties,specialty_id',
            'experience' => 'nullable|string',
            'license_number' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $newUser = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => $request->role,
            ]);

            // Create profile based on role
            if ($request->role === 'doctor') {
                DoctorProfile::create([
                    'doctor_id' => Str::uuid(),
                    'user_id' => $newUser->user_id,
                    'fullname' => $request->fullname,
                    'specialty_id' => $request->specialty_id,
                    'experience' => $request->experience,
                    'license_number' => $request->license_number,
                ]);
            } elseif ($request->role === 'patient') {
                PatientProfile::create([
                    'patient_id' => Str::uuid(),
                    'user_id' => $newUser->user_id,
                    'fullname' => $request->fullname,
                    'dob' => $request->dob,
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'medical_history' => $request->medical_history,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $newUser->load(['doctorProfile', 'patientProfile'])
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user information.
     */
    public function updateUser(Request $request, $userId)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        $targetUser = User::find($userId);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|required|string|max:255|unique:users,username,' . $userId . ',user_id',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $userId . ',user_id',
            'phone' => 'nullable|string|max:20',
            'role' => 'sometimes|required|in:patient,doctor,admin',
            'fullname' => 'sometimes|required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'medical_history' => 'nullable|string',
            'specialty_id' => 'nullable|exists:specialties,specialty_id',
            'experience' => 'nullable|string',
            'license_number' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update user basic info
            $targetUser->update([
                'username' => $request->get('username', $targetUser->username),
                'email' => $request->get('email', $targetUser->email),
                'phone' => $request->get('phone', $targetUser->phone),
                'role' => $request->get('role', $targetUser->role),
            ]);

            // Update profile based on role
            if ($targetUser->isDoctor() && $targetUser->doctorProfile) {
                $targetUser->doctorProfile()->update([
                    'fullname' => $request->get('fullname', $targetUser->doctorProfile->fullname),
                    'specialty_id' => $request->get('specialty_id', $targetUser->doctorProfile->specialty_id),
                    'experience' => $request->get('experience', $targetUser->doctorProfile->experience),
                    'license_number' => $request->get('license_number', $targetUser->doctorProfile->license_number),
                ]);
            } elseif ($targetUser->isPatient() && $targetUser->patientProfile) {
                $targetUser->patientProfile()->update([
                    'fullname' => $request->get('fullname', $targetUser->patientProfile->fullname),
                    'dob' => $request->get('dob', $targetUser->patientProfile->dob),
                    'gender' => $request->get('gender', $targetUser->patientProfile->gender),
                    'address' => $request->get('address', $targetUser->patientProfile->address),
                    'medical_history' => $request->get('medical_history', $targetUser->patientProfile->medical_history),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $targetUser->load(['doctorProfile', 'patientProfile'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user.
     */
    public function deleteUser($userId)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        $targetUser = User::find($userId);
        if (!$targetUser) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Prevent admin from deleting themselves
        if ($targetUser->user_id === $user->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 400);
        }

        try {
            $targetUser->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'User deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all appointments.
     */
    public function getAppointments(Request $request)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Admin role required.'
            ], 403);
        }

        try {
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status', '');

            $query = Appointment::with(['patient', 'doctor.user']);

            if ($status) {
                $query->where('status', $status);
            }

            $appointments = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve appointments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
