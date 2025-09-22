<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    use HasFactory;

    protected $table = 'specialties';
    protected $primaryKey = 'specialty_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'specialty_id',
        'name',
        'description',
    ];

    /**
     * Get the doctors that belong to this specialty.
     */
    public function doctors(): HasMany
    {
        return $this->hasMany(DoctorProfile::class, 'specialty_id', 'specialty_id');
    }
}
