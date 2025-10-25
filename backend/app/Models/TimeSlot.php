<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'is_available',
        'max_capacity',
        'current_bookings'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean',
        'max_capacity' => 'integer',
        'current_bookings' => 'integer'
    ];

    protected $appends = ['remaining_capacity'];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'doctor_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'time_slot_id');
    }

    /**
     * Generate time slots for a doctor for the next 7 days
     */
    public static function generateSlotsForDoctor($doctorId, $startDate = null)
    {
        $startDate = $startDate ?: now()->toDateString();

        // Bắt đầu từ ngày mai (i = 1)
        for ($i = 1; $i <= 7; $i++) {
            $date = date('Y-m-d', strtotime($startDate . " +{$i} days"));
            $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 6 = Saturday

            // Skip Sunday (0)
            if ($dayOfWeek == 0) {
                continue;
            }

            // Morning slots: 8:00 - 10:30 (30 min intervals) - Available for Monday to Saturday morning
            $morningSlots = self::generateSlotsForPeriod($date, '08:00', '10:30', 30);

            // Afternoon slots: 14:00 - 16:30 (30 min intervals) - Only Monday to Friday (not Saturday)
            $afternoonSlots = [];
            if ($dayOfWeek != 6) { // Not Saturday (Saturday = 6)
                $afternoonSlots = self::generateSlotsForPeriod($date, '14:00', '16:30', 30);
            }

            $allSlots = array_merge($morningSlots, $afternoonSlots);

            foreach ($allSlots as $slot) {
                // Check if slot already exists
                $existingSlot = self::where('doctor_id', $doctorId)
                    ->where('date', $date)
                    ->where('start_time', $slot['start_time'])
                    ->first();

                if ($existingSlot) {
                    // Only update end_time and max_capacity, keep current_bookings intact
                    $existingSlot->update([
                        'end_time' => $slot['end_time'],
                        'max_capacity' => 5,
                        // DO NOT reset current_bookings!
                    ]);
                } else {
                    // Create new slot with current_bookings = 0
                    self::create([
                        'doctor_id' => $doctorId,
                        'date' => $date,
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'is_available' => true,
                        'max_capacity' => 5,
                        'current_bookings' => 0
                    ]);
                }
            }
        }
    }

    /**
     * Generate slots for a specific time period
     */
    private static function generateSlotsForPeriod($date, $startTime, $endTime, $intervalMinutes)
    {
        $slots = [];
        $start = strtotime($date . ' ' . $startTime);
        $end = strtotime($date . ' ' . $endTime);

        for ($time = $start; $time < $end; $time += ($intervalMinutes * 60)) {
            $slots[] = [
                'start_time' => date('H:i:s', $time),
                'end_time' => date('H:i:s', $time + ($intervalMinutes * 60))
            ];
        }

        return $slots;
    }

    /**
     * Get all time slots for a doctor on a specific date (including fully booked)
     */
    public static function getAvailableSlots($doctorId, $date)
    {
        return self::where('doctor_id', $doctorId)
            ->where('date', $date)
            // Don't filter by is_available or capacity - show all slots
            // Frontend will disable button when remaining_capacity = 0
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get available time slots for next 7 days
     */
    public static function getAvailableSlotsForWeek($doctorId, $startDate = null)
    {
        $startDate = $startDate ?: now()->toDateString();
        $slots = [];

        // Bắt đầu từ ngày mai (i = 1)
        for ($i = 1; $i <= 7; $i++) {
            $date = date('Y-m-d', strtotime($startDate . " +{$i} days"));
            $daySlots = self::getAvailableSlots($doctorId, $date);

            if ($daySlots->count() > 0) {
                $slots[$date] = $daySlots;
            }
        }

        return $slots;
    }

    /**
     * Increment current bookings count
     */
    public function incrementBookings()
    {
        $this->increment('current_bookings');

        // Mark as unavailable if capacity is reached
        if ($this->current_bookings >= $this->max_capacity) {
            $this->update(['is_available' => false]);
        }
    }

    /**
     * Decrement current bookings count
     */
    public function decrementBookings()
    {
        $this->decrement('current_bookings');

        // Mark as available if there's space
        if ($this->current_bookings < $this->max_capacity) {
            $this->update(['is_available' => true]);
        }
    }

    /**
     * Get remaining capacity
     */
    public function getRemainingCapacityAttribute()
    {
        return $this->max_capacity - $this->current_bookings;
    }
}
