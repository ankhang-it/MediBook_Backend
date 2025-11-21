<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorInstruction extends Model
{
    use HasFactory;

    protected $table = 'doctor_instructions';
    protected $primaryKey = 'instruction_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'instruction_id',
        'appointment_id',
        'doctor_id',
        'instructions',
        'reminders',
        'notes',
    ];

    protected $casts = [
        'instructions' => 'array',
        'reminders' => 'array',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'doctor_id');
    }
}

