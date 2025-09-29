<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PatientProfile;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    /**
     * Get patient profile information.
     */
    public function getProfile()
    {
        $user = auth()->user();
        
        if (!$user->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Patient role required.'
            ], 403);
        }

        $patientProfile = $user->patientProfile;
        
        if (!$patientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                ],
                'profile' => [
                    'patient_id' => $patientProfile->patient_id,
                    'fullname' => $patientProfile->fullname,
                    'dob' => $patientProfile->dob,
                    'gender' => $patientProfile->gender,
                    'address' => $patientProfile->address,
                    'medical_history' => $patientProfile->medical_history,
                    'age' => $patientProfile->age,
                ]
            ]
        ]);
    }

    /**
     * Update patient profile information.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Patient role required.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'medical_history' => 'nullable|string',
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
            $user->update([
                'phone' => $request->phone,
            ]);

            // Update patient profile
            $user->patientProfile()->update([
                'fullname' => $request->fullname,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'address' => $request->address,
                'medical_history' => $request->medical_history,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload patient avatar.
     */
    public function uploadAvatar(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Patient role required.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . basename($user->avatar));
            }

            // Store new avatar
            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->user_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('avatars', $filename, 'public');
            
            // Update user avatar path
            $user->update([
                'avatar' => Storage::url($path)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'data' => [
                    'avatar_url' => $user->avatar
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient medical history.
     */
    public function getMedicalHistory(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Patient role required.'
            ], 403);
        }

        $patientProfile = $user->patientProfile;
        
        if (!$patientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found'
            ], 404);
        }

        try {
            // Get medical records through appointments
            $medicalRecords = MedicalRecord::with([
                'appointment' => function($query) use ($patientProfile) {
                    $query->where('patient_id', $patientProfile->patient_id);
                },
                'doctor' => function($query) {
                    $query->with('user');
                }
            ])
            ->whereHas('appointment', function($query) use ($patientProfile) {
                $query->where('patient_id', $patientProfile->patient_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

            // Get appointments with medical records
            $appointments = Appointment::with([
                'doctor' => function($query) {
                    $query->with('user');
                },
                'medicalRecord'
            ])
            ->where('patient_id', $patientProfile->patient_id)
            ->orderBy('appointment_date', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'medical_records' => $medicalRecords,
                    'appointments' => $appointments
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve medical history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get patient appointments.
     */
    public function getAppointments(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->isPatient()) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. Patient role required.'
            ], 403);
        }

        $patientProfile = $user->patientProfile;
        
        if (!$patientProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found'
            ], 404);
        }

        try {
            $appointments = Appointment::with([
                'doctor' => function($query) {
                    $query->with('user');
                },
                'medicalRecord'
            ])
            ->where('patient_id', $patientProfile->patient_id)
            ->orderBy('appointment_date', 'desc')
            ->get();

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
