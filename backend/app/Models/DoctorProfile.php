<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorProfile extends Model
{
    use HasFactory;

    protected $table = 'doctor_profiles';
    protected $primaryKey = 'doctor_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'doctor_id',
        'user_id',
        'fullname',
        'specialty_id',
        'experience',
        'license_number',
        'schedule',
    ];

    protected function casts(): array
    {
        return [
            'schedule' => 'array',
        ];
    }

    /**
     * Get the user that owns the doctor profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the specialty that the doctor belongs to.
     */
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id', 'specialty_id');
    }

    /**
     * Get the appointments for the doctor.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the medical records for the doctor.
     */
    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the feedback for the doctor.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the average rating for the doctor.
     */
    public function getAverageRatingAttribute()
    {
        return $this->feedback()->avg('rating') ?? 0;
    }

    /**
     * Get the total number of reviews for the doctor.
     */
    public function getTotalReviewsAttribute()
    {
        return $this->feedback()->count();
    }
}
