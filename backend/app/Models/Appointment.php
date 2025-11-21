<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $table = 'appointments';
    protected $primaryKey = 'appointment_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'time_slot_id',
        'schedule_time',
        'status',
        'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'schedule_time' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the patient that owns the appointment.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(PatientProfile::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the doctor that owns the appointment.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the time slot for the appointment.
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class, 'time_slot_id');
    }

    /**
     * Get the medical record for the appointment.
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Get the doctor's instructions for the appointment.
     */
    public function doctorInstruction(): HasOne
    {
        return $this->hasOne(DoctorInstruction::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Get the payment for the appointment.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // When appointment is created, increment time slot bookings
        static::created(function ($appointment) {
            // Ensure timeSlot relationship is loaded
            $appointment->load('timeSlot');

            if ($appointment->timeSlot) {
                $beforeBookings = $appointment->timeSlot->current_bookings;

                \Log::info('📈 Incrementing bookings for time slot', [
                    'time_slot_id' => $appointment->time_slot_id,
                    'before_current_bookings' => $beforeBookings,
                    'max_capacity' => $appointment->timeSlot->max_capacity
                ]);

                // Increment bookings - this updates the database directly
                $appointment->timeSlot->increment('current_bookings');

                // Calculate values from memory (don't refresh from DB within transaction)
                $afterBookings = $beforeBookings + 1;
                $remainingCapacity = $appointment->timeSlot->max_capacity - $afterBookings;

                \Log::info('✅ Bookings incremented', [
                    'time_slot_id' => $appointment->time_slot_id,
                    'after_current_bookings' => $afterBookings,
                    'remaining_capacity' => $remainingCapacity
                ]);

                // Mark as unavailable if full
                if ($afterBookings >= $appointment->timeSlot->max_capacity) {
                    $appointment->timeSlot->update(['is_available' => false]);
                    \Log::info('🔴 Time slot marked as unavailable (full)', [
                        'time_slot_id' => $appointment->time_slot_id
                    ]);
                }
            } else {
                \Log::warning('⚠️ TimeSlot not found for appointment', [
                    'appointment_id' => $appointment->appointment_id,
                    'time_slot_id' => $appointment->time_slot_id
                ]);
            }
        });

        // When appointment is cancelled, decrement time slot bookings
        static::updated(function ($appointment) {
            if ($appointment->isDirty('status')) {
                $oldStatus = $appointment->getOriginal('status');
                $newStatus = $appointment->status;

                // If status changed from active to cancelled
                if (in_array($oldStatus, ['pending', 'confirmed']) && $newStatus === 'cancelled') {
                    if ($appointment->timeSlot) {
                        $appointment->timeSlot->decrementBookings();
                    }
                }
                // If status changed from cancelled to active
                elseif ($oldStatus === 'cancelled' && in_array($newStatus, ['pending', 'confirmed'])) {
                    if ($appointment->timeSlot) {
                        $appointment->timeSlot->incrementBookings();
                    }
                }
            }
        });

        // When appointment is deleted, decrement time slot bookings
        static::deleted(function ($appointment) {
            if ($appointment->timeSlot && in_array($appointment->status, ['pending', 'confirmed'])) {
                $appointment->timeSlot->decrementBookings();
            }
        });
    }
}
