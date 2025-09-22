<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientProfile extends Model
{
    use HasFactory;

    protected $table = 'patient_profiles';
    protected $primaryKey = 'patient_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'patient_id',
        'user_id',
        'fullname',
        'dob',
        'gender',
        'address',
        'medical_history',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    /**
     * Get the user that owns the patient profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the appointments for the patient.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the feedback given by the patient.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'patient_id', 'patient_id');
    }

    /**
     * Get the medical records for the patient.
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id', 'patient_id');
    }

    /**
     * Calculate age from date of birth.
     */
    public function getAgeAttribute()
    {
        return $this->dob ? $this->dob->age : null;
    }
}
