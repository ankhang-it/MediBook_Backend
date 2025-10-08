<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Appointment;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Get doctor profile
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();
            $doctor = DoctorProfile::with(['user', 'specialty', 'feedback'])
                ->where('user_id', $user->user_id)
                ->first();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve doctor profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update doctor profile
     */
    public function updateProfile(Request $request)
    {
        try {
            // Debug: Check authentication
            \Log::info('Auth check:', [
                'user' => Auth::user(),
                'guard' => Auth::getDefaultDriver(),
                'check' => Auth::check()
            ]);

            $user = Auth::user();
            if (!$user) {
                \Log::error('No authenticated user found');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $doctor = DoctorProfile::where('user_id', $user->user_id)->first();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }

            // Log request data for debugging
            \Log::info('Update Profile Request:', [
                'all_data' => $request->all(),
                'input_data' => $request->input(),
                'files' => $request->allFiles(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method(),
                'raw_content_length' => strlen($request->getContent()),
                'has_fullname' => $request->has('fullname'),
                'has_email' => $request->has('email'),
                'has_phone' => $request->has('phone'),
                'has_experience' => $request->has('experience'),
                'has_consultation_fee' => $request->has('consultation_fee'),
                'has_bio' => $request->has('bio'),
                'has_avatar' => $request->hasFile('avatar'),
                'filled_fullname' => $request->filled('fullname'),
                'filled_email' => $request->filled('email'),
            ]);

            // Debug: Check if we can access the file directly
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                \Log::info('Avatar file info:', [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'is_valid' => $file->isValid(),
                    'error' => $file->getError()
                ]);
            }

            // Validate request
            try {
                $request->validate([
                    'fullname' => 'sometimes|string|max:255',
                    'email' => 'sometimes|email|max:255',
                    'phone' => 'sometimes|string|max:20',
                    'experience' => 'sometimes|string|max:500',
                    'consultation_fee' => 'sometimes|numeric|min:0',
                    'bio' => 'sometimes|string|max:1000',
                    'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);
                \Log::info('Validation passed');
            } catch (\Illuminate\Validation\ValidationException $e) {
                \Log::error('Validation failed:', $e->errors());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }

            // Update user information (users table)
            $userData = [];
            if ($request->filled('fullname')) {
                $userData['username'] = $request->fullname; // username trong users table
            }
            if ($request->filled('email')) {
                $userData['email'] = $request->email;
            }
            if ($request->filled('phone')) {
                $userData['phone'] = $request->phone;
            }

            // Handle avatar upload for user
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
                $avatar->move(public_path('uploads/avatars'), $avatarName);
                $userData['avatar'] = 'uploads/avatars/' . $avatarName;
            }

            if (!empty($userData)) {
                \Log::info('Updating user data:', $userData);
                try {
                    $user->update($userData);
                    \Log::info('User updated successfully');
                } catch (\Exception $e) {
                    \Log::error('Failed to update user:', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update user information: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Update doctor profile (doctor_profiles table)
            $doctorData = [];
            if ($request->filled('fullname')) {
                $doctorData['fullname'] = $request->fullname; // fullname trong doctor_profiles table
            }
            if ($request->filled('experience')) {
                $doctorData['experience'] = $request->experience;
            }
            if ($request->filled('consultation_fee')) {
                $doctorData['consultation_fee'] = $request->consultation_fee;
            }
            if ($request->filled('bio')) {
                $doctorData['bio'] = $request->bio;
            }

            if (!empty($doctorData)) {
                \Log::info('Updating doctor data:', $doctorData);
                try {
                    $doctor->update($doctorData);
                    \Log::info('Doctor profile updated successfully');
                } catch (\Exception $e) {
                    \Log::error('Failed to update doctor profile:', ['error' => $e->getMessage()]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to update doctor profile: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Reload doctor with relationships
            $doctor = DoctorProfile::with(['user', 'specialty', 'feedback'])
                ->where('doctor_id', $doctor->doctor_id)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $doctor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor appointments
     */
    public function getAppointments(Request $request)
    {
        try {
            $user = Auth::user();
            $doctor = DoctorProfile::where('user_id', $user->user_id)->first();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }

            $appointments = Appointment::with(['patient.user', 'doctor.user'])
                ->where('doctor_id', $doctor->doctor_id)
                ->orderBy('schedule_time', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $appointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve appointments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update appointment status
     */
    public function updateAppointmentStatus(Request $request, $appointmentId)
    {
        try {
            $user = Auth::user();
            $doctor = DoctorProfile::where('user_id', $user->user_id)->first();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }

            $appointment = Appointment::where('appointment_id', $appointmentId)
                ->where('doctor_id', $doctor->doctor_id)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            $appointment->status = $request->status;
            $appointment->save();

            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated successfully',
                'data' => $appointment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor statistics
     */
    public function getStatistics()
    {
        try {
            $user = Auth::user();
            $doctor = DoctorProfile::where('user_id', $user->user_id)->first();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }

            $totalAppointments = Appointment::where('doctor_id', $doctor->doctor_id)->count();
            $todayAppointments = Appointment::where('doctor_id', $doctor->doctor_id)
                ->whereDate('schedule_time', today())
                ->count();
            $pendingAppointments = Appointment::where('doctor_id', $doctor->doctor_id)
                ->where('status', 'pending')
                ->count();
            $completedAppointments = Appointment::where('doctor_id', $doctor->doctor_id)
                ->where('status', 'completed')
                ->count();

            // Calculate average rating
            $averageRating = Feedback::where('doctor_id', $doctor->doctor_id)
                ->avg('rating') ?? 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_appointments' => $totalAppointments,
                    'today_appointments' => $todayAppointments,
                    'pending_appointments' => $pendingAppointments,
                    'completed_appointments' => $completedAppointments,
                    'average_rating' => round($averageRating, 1)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
