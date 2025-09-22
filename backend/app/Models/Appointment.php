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
     * Get the medical record for the appointment.
     */
    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Get the payment for the appointment.
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'appointment_id', 'appointment_id');
    }
}
