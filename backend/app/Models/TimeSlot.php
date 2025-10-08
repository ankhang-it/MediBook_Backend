<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TimeSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'date',
        'start_time',
        'end_time',
        'is_available'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean'
    ];

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'doctor_id');
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'time_slot_id');
    }

    /**
     * Generate time slots for a doctor for the next 7 days
     */
    public static function generateSlotsForDoctor($doctorId, $startDate = null)
    {
        $startDate = $startDate ?: now()->toDateString();

        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($startDate . " +{$i} days"));
            $dayOfWeek = date('w', strtotime($date)); // 0 = Sunday, 6 = Saturday

            // Skip Sunday (0)
            if ($dayOfWeek == 0) {
                continue;
            }

            // Morning slots: 8:00 - 10:30 (30 min intervals) - Available for all weekdays
            $morningSlots = self::generateSlotsForPeriod($date, '08:00', '10:30', 30);

            // Afternoon slots: 14:00 - 16:30 (30 min intervals) - Only Monday to Friday
            $afternoonSlots = [];
            if ($dayOfWeek != 6) { // Not Saturday
                $afternoonSlots = self::generateSlotsForPeriod($date, '14:00', '16:30', 30);
            }

            $allSlots = array_merge($morningSlots, $afternoonSlots);

            foreach ($allSlots as $slot) {
                self::updateOrCreate(
                    [
                        'doctor_id' => $doctorId,
                        'date' => $date,
                        'start_time' => $slot['start_time']
                    ],
                    [
                        'end_time' => $slot['end_time'],
                        'is_available' => true
                    ]
                );
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
     * Get available time slots for a doctor on a specific date
     */
    public static function getAvailableSlots($doctorId, $date)
    {
        return self::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('is_available', true)
            ->whereDoesntHave('appointment', function ($query) {
                $query->whereIn('status', ['pending', 'confirmed']);
            })
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

        for ($i = 0; $i < 7; $i++) {
            $date = date('Y-m-d', strtotime($startDate . " +{$i} days"));
            $daySlots = self::getAvailableSlots($doctorId, $date);

            if ($daySlots->count() > 0) {
                $slots[$date] = $daySlots;
            }
        }

        return $slots;
    }
}
