<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class TimeSlotController extends Controller
{
    /**
     * Get available time slots for a doctor
     */
    public function getAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|string',
            'date' => 'nullable|date',
            'days' => 'nullable|integer|min:1|max:7'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $doctorId = $request->doctor_id;
            $date = $request->date ?: now()->toDateString();
            $days = $request->days ?: 7;

            // Check if doctor exists
            $doctor = DoctorProfile::find($doctorId);
            if (!$doctor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Doctor not found'
                ], 404);
            }

            // Generate slots if they don't exist
            TimeSlot::generateSlotsForDoctor($doctorId, $date);

            // Get available slots (bắt đầu từ ngày mai)
            $slots = [];
            for ($i = 1; $i <= $days; $i++) {
                $currentDate = date('Y-m-d', strtotime($date . " +{$i} days"));
                $daySlots = TimeSlot::getAvailableSlots($doctorId, $currentDate);

                if ($daySlots->count() > 0) {
                    $slots[$currentDate] = $daySlots->map(function ($slot) {
                        return [
                            'id' => $slot->id,
                            'start_time' => $slot->start_time,
                            'end_time' => $slot->end_time,
                            'formatted_time' => $this->formatTimeSlot($slot->start_time, $slot->end_time),
                            'max_capacity' => $slot->max_capacity,
                            'current_bookings' => $slot->current_bookings,
                            'remaining_capacity' => $slot->remaining_capacity
                        ];
                    });
                }
            }

            return response()->json([
                'success' => true,
                'data' => $slots
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve time slots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Book a time slot
     */
    public function bookSlot(Request $request)
    {
        // Debug logging
        Log::info('BookSlot request data:', [
            'request_data' => $request->all(),
            'time_slot_id' => $request->time_slot_id,
            'patient_id' => $request->patient_id,
            'reason' => $request->reason
        ]);

        $validator = Validator::make($request->all(), [
            'time_slot_id' => 'required|integer|exists:time_slots,id',
            'patient_id' => 'required|string|exists:patient_profiles,patient_id',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            Log::error('BookSlot validation failed:', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            // Use database transaction to prevent race conditions
            DB::beginTransaction();

            // Lock the time slot row for update to prevent race conditions
            $timeSlot = TimeSlot::where('id', $request->time_slot_id)
                ->lockForUpdate()
                ->first();

            if (!$timeSlot) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot not found'
                ], 404);
            }

            // Check if slot is fully booked
            if ($timeSlot->current_bookings >= $timeSlot->max_capacity) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Time slot is fully booked'
                ], 400);
            }

            // Create appointment with schedule_time from time slot
            // schedule_time = date + start_time from time slot
            $scheduleTime = \Carbon\Carbon::parse($timeSlot->date->format('Y-m-d') . ' ' . $timeSlot->start_time->format('H:i:s'));

            $appointment = \App\Models\Appointment::create([
                'appointment_id' => \Illuminate\Support\Str::uuid(),
                'patient_id' => $request->patient_id,
                'doctor_id' => $timeSlot->doctor_id,
                'time_slot_id' => $timeSlot->id,
                'schedule_time' => $scheduleTime, // Lưu đúng ngày và giờ từ time slot
                'status' => 'pending', // Chờ bác sĩ duyệt
                'payment_status' => 'unpaid'
            ]);

            // Note: incrementBookings() is called automatically via model event

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => $appointment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BookSlot error:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to book appointment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format time slot for display
     */
    private function formatTimeSlot($startTime, $endTime)
    {
        $start = date('H:i', strtotime($startTime));
        $end = date('H:i', strtotime($endTime));

        // Determine if it's morning or afternoon
        $hour = (int) date('H', strtotime($startTime));
        $period = $hour < 12 ? 'Sáng' : 'Chiều';

        return [
            'time' => "{$start} - {$end}",
            'period' => $period,
            'display' => "{$period} {$start} - {$end}"
        ];
    }
}
