<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_records';
    protected $primaryKey = 'record_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'record_id',
        'appointment_id',
        'doctor_id',
        'diagnosis',
        'prescription',
        'notes',
        'lab_result_file_path',
    ];

    protected $appends = [
        'lab_result_file_url',
        'lab_result_file_name',
        'lab_result_files',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the appointment that owns the medical record.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Get the doctor that owns the medical record.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_id', 'doctor_id');
    }

    /**
     * Get the files associated with the medical record.
     */
    public function files(): HasMany
    {
        return $this->hasMany(MedicalRecordFile::class, 'medical_record_id', 'record_id');
    }

    /**
     * Accessor for lab result file url.
     */
    public function getLabResultFileUrlAttribute(): ?string
    {
        return $this->lab_result_file_path
            ? asset($this->lab_result_file_path)
            : null;
    }

    /**
     * Accessor for lab result file name.
     */
    public function getLabResultFileNameAttribute(): ?string
    {
        return $this->lab_result_file_path
            ? basename($this->lab_result_file_path)
            : null;
    }

    /**
     * Accessor to get attached lab result files.
     */
    public function getLabResultFilesAttribute()
    {
        return $this->relationLoaded('files')
            ? $this->files->map(function ($file) {
                return [
                    'file_id' => $file->file_id,
                    'file_name' => $file->file_name,
                    'url' => $file->url,
                    'mime_type' => $file->mime_type,
                    'file_size' => $file->file_size,
                ];
            })
            : [];
    }
}
