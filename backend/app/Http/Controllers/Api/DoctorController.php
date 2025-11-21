<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordFile;
use App\Models\DoctorInstruction;
use App\Mail\AppointmentConfirmedPatient;
use App\Mail\AppointmentConfirmedDoctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    /**
     * Get doctor profile
     */
    public function getProfile()
    {
        try {
            $user = Auth::user();
            $doctor = DoctorProfile::with(['user', 'specialty', 'feedback.patient.user'])
                ->where('user_id', $user->user_id)
                ->first();

            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor profile not found'
                ], 404);
            }

            // Add rating information
            $doctor->average_rating = $doctor->average_rating;
            $doctor->total_reviews = $doctor->total_reviews;
            $doctor->rating_breakdown = $doctor->rating_breakdown;
            $doctor->recent_feedback = $doctor->recent_feedback;

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

            // Get filter parameters
            $status = $request->get('status');
            $date = $request->get('date'); // Filter by specific date
            $today = $request->get('today', false); // Get only today's appointments

            $query = Appointment::with([
                'patient' => function ($query) {
                    $query->with('user');
                },
                'doctor' => function ($query) {
                    $query->with('user');
                },
                'timeSlot', // Include time slot to get exact time
                'medicalRecord.files', // Include medical record with files
                'doctorInstruction',
            ])
                ->where('doctor_id', $doctor->doctor_id);

            // Filter by status if provided
            if ($status) {
                $query->where('status', $status);
            }

            // Filter by date if provided
            if ($date) {
                $query->whereDate('schedule_time', $date);
            }

            // Filter today's appointments
            if ($today) {
                $query->whereDate('schedule_time', today());
            }

            $appointments = $query->orderBy('schedule_time', 'asc') // Sort by schedule time ascending
                ->get();

            // Format appointments with proper date and time from schedule_time
            $formattedAppointments = $appointments->map(function ($appointment) {
                $scheduleTime = \Carbon\Carbon::parse($appointment->schedule_time);
                
                return [
                    'appointment_id' => $appointment->appointment_id,
                    'patient' => $appointment->patient,
                    'doctor' => $appointment->doctor,
                    'time_slot_id' => $appointment->time_slot_id,
                    'timeSlot' => $appointment->timeSlot,
                    'schedule_time' => $appointment->schedule_time,
                    'schedule_date' => $scheduleTime->format('Y-m-d'),
                    'schedule_time_formatted' => $scheduleTime->format('H:i'),
                    'schedule_datetime_formatted' => $scheduleTime->format('d/m/Y H:i'),
                    'status' => $appointment->status,
                    'payment_status' => $appointment->payment_status,
                    'medicalRecord' => $appointment->medicalRecord,
                    'doctor_instruction' => $appointment->doctorInstruction,
                    'created_at' => $appointment->created_at,
                    'updated_at' => $appointment->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedAppointments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve appointments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve appointment (change from pending to confirmed)
     */
    public function approveAppointment($appointmentId)
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

            $appointment = Appointment::with(['patient.user', 'doctor.user', 'timeSlot'])
                ->where('appointment_id', $appointmentId)
                ->where('doctor_id', $doctor->doctor_id)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            // Only allow approving pending appointments
            if ($appointment->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending appointments can be approved'
                ], 400);
            }

            // Update status to confirmed
            $appointment->status = 'confirmed';
            $appointment->save();

            // Send email notifications
            try {
                // Send email to patient
                if ($appointment->patient && $appointment->patient->user && $appointment->patient->user->email) {
                    Mail::to($appointment->patient->user->email)
                        ->send(new AppointmentConfirmedPatient($appointment));
                    \Log::info('Confirmation email sent to patient', [
                        'patient_email' => $appointment->patient->user->email
                    ]);
                }

                // Send email to doctor
                if ($appointment->doctor && $appointment->doctor->user && $appointment->doctor->user->email) {
                    Mail::to($appointment->doctor->user->email)
                        ->send(new AppointmentConfirmedDoctor($appointment));
                    \Log::info('Notification email sent to doctor', [
                        'doctor_email' => $appointment->doctor->user->email
                    ]);
                }
            } catch (\Exception $e) {
                // Log email error but don't fail the transaction
                \Log::error('Failed to send appointment confirmation emails', [
                    'error' => $e->getMessage(),
                    'appointment_id' => $appointment->appointment_id
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Appointment approved successfully',
                'data' => $appointment->load(['patient.user', 'doctor.user', 'timeSlot'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save doctor's instructions for an appointment.
     */
    public function saveAppointmentInstructions(Request $request, $appointmentId)
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

            $appointment = Appointment::with('doctorInstruction')
                ->where('appointment_id', $appointmentId)
                ->where('doctor_id', $doctor->doctor_id)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            if (!in_array($appointment->status, ['confirmed', 'completed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instructions can only be added for confirmed or completed appointments'
                ], 400);
            }

            $validated = $request->validate([
                'instructions' => 'required|array|min:1',
                'instructions.*.name' => 'required|string|max:255',
                'instructions.*.note' => 'nullable|string|max:500',
                'reminders' => 'nullable|array',
                'reminders.*' => 'nullable|string|max:500',
                'notes' => 'nullable|string',
            ]);

            $instruction = DoctorInstruction::firstOrNew([
                'appointment_id' => $appointment->appointment_id,
            ]);

            if (!$instruction->instruction_id) {
                $instruction->instruction_id = Str::uuid();
            }

            $instruction->doctor_id = $doctor->doctor_id;
            $instruction->instructions = $validated['instructions'];
            $instruction->reminders = $validated['reminders'] ?? [];
            $instruction->notes = $validated['notes'] ?? null;
            $instruction->save();

            return response()->json([
                'success' => true,
                'message' => 'Instructions saved successfully',
                'data' => $instruction
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save instructions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get doctor's instructions for an appointment.
     */
    public function getAppointmentInstructions($appointmentId)
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

            $instruction = DoctorInstruction::where('appointment_id', $appointmentId)
                ->where('doctor_id', $doctor->doctor_id)
                ->first();

            if (!$instruction) {
                return response()->json([
                    'success' => false,
                    'message' => 'No instructions found for this appointment'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $instruction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve instructions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update appointment status (for changing status after approval)
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

            $request->validate([
                'status' => 'required|in:pending,confirmed,cancelled,completed'
            ]);

            $appointment = Appointment::where('appointment_id', $appointmentId)
                ->where('doctor_id', $doctor->doctor_id)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            $oldStatus = $appointment->status;
            $newStatus = $request->status;

            // Validate status transition
            // Can only change from: pending -> confirmed -> completed
            // Or cancel from any status
            if ($newStatus === 'cancelled') {
                // Can cancel from any status
            } elseif ($oldStatus === 'pending' && $newStatus === 'confirmed') {
                // Use approveAppointment method instead
                return response()->json([
                    'success' => false,
                    'message' => 'Please use the approve endpoint to approve appointments'
                ], 400);
            } elseif ($oldStatus === 'confirmed' && $newStatus === 'completed') {
                // Can complete confirmed appointments
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition'
                ], 400);
            }

            $appointment->status = $newStatus;
            $appointment->save();

            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated successfully',
                'data' => $appointment->load(['patient.user', 'doctor.user', 'timeSlot'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create medical record for completed appointment
     */
    public function createMedicalRecord(Request $request, $appointmentId)
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

            // Validate request
            $request->validate([
                'diagnosis' => 'required|string|max:1000',
                'prescription' => 'required|string|max:1000',
                'notes' => 'nullable|string|max:1000',
                'lab_result_files' => 'nullable|array|max:3',
                'lab_result_files.*' => 'file|mimes:pdf,jpeg,jpg,png|max:5120',
            ]);

            // Check if appointment exists and belongs to this doctor
            $appointment = Appointment::where('appointment_id', $appointmentId)
                ->where('doctor_id', $doctor->doctor_id)
                ->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found'
                ], 404);
            }

            // Check if appointment is completed or confirmed (allow creating record for confirmed appointments too)
            if (!in_array($appointment->status, ['completed', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment must be confirmed or completed before creating medical record'
                ], 400);
            }

            // Check if medical record already exists
            $existingRecord = MedicalRecord::where('appointment_id', $appointmentId)->first();
            if ($existingRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Medical record already exists for this appointment'
                ], 400);
            }

            // Handle lab result file upload
            $storedFiles = [];
            if ($request->hasFile('lab_result_files')) {
                $destinationPath = public_path('uploads/lab-results');

                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }

                foreach ($request->file('lab_result_files') as $upload) {
                    $originalName = $upload->getClientOriginalName();
                    $extension = $upload->getClientOriginalExtension();
                    $mimeType = $upload->getClientMimeType();
                    $fileSize = $upload->getSize();

                    $generatedName = now()->format('YmdHis') . '_' . Str::uuid() . '.' . $extension;
                    $upload->move($destinationPath, $generatedName);

                    $storedFiles[] = [
                        'file_name' => $originalName,
                        'file_path' => 'uploads/lab-results/' . $generatedName,
                        'mime_type' => $mimeType,
                        'file_size' => $fileSize,
                    ];
                }
            }

            // Create medical record
            $medicalRecord = MedicalRecord::create([
                'record_id' => \Illuminate\Support\Str::uuid(),
                'appointment_id' => $appointmentId,
                'doctor_id' => $doctor->doctor_id,
                'diagnosis' => $request->diagnosis,
                'prescription' => $request->prescription,
                'notes' => $request->notes,
                'lab_result_file_path' => $storedFiles[0]['file_path'] ?? null,
            ]);

            foreach ($storedFiles as $fileData) {
                MedicalRecordFile::create([
                    'file_id' => Str::uuid(),
                    'medical_record_id' => $medicalRecord->record_id,
                    'file_name' => $fileData['file_name'],
                    'file_path' => $fileData['file_path'],
                    'mime_type' => $fileData['mime_type'],
                    'file_size' => $fileData['file_size'],
                ]);
            }

            $medicalRecord->load('files');

            return response()->json([
                'success' => true,
                'message' => 'Medical record created successfully',
                'data' => $medicalRecord
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create medical record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all doctors with rating information
     */
    public function getAllDoctors()
    {
        try {
            $doctors = DoctorProfile::with(['user', 'specialty', 'feedback'])
                ->get()
                ->map(function ($doctor) {
                    return [
                        'doctor_id' => $doctor->doctor_id,
                        'fullname' => $doctor->fullname,
                        'specialty' => $doctor->specialty,
                        'experience' => $doctor->experience,
                        'consultation_fee' => $doctor->consultation_fee,
                        'bio' => $doctor->bio,
                        'user' => $doctor->user,
                        'average_rating' => $doctor->average_rating,
                        'total_reviews' => $doctor->total_reviews,
                        'rating_breakdown' => $doctor->rating_breakdown,
                        'created_at' => $doctor->created_at,
                        'updated_at' => $doctor->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $doctors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve doctors: ' . $e->getMessage()
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
                    'average_rating' => round($averageRating, 1),
                    'total_reviews' => $doctor->total_reviews,
                    'rating_breakdown' => $doctor->rating_breakdown
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
