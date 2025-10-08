<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;
use App\Models\DoctorProfile;

class RegenerateTimeSlotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing time slots
        TimeSlot::truncate();
        
        // Get all doctors
        $doctors = DoctorProfile::all();
        
        foreach ($doctors as $doctor) {
            // Generate time slots for each doctor
            TimeSlot::generateSlotsForDoctor($doctor->doctor_id);
            
            $this->command->info("Generated time slots for doctor: {$doctor->fullname}");
        }
        
        $this->command->info('Time slots regenerated successfully with new schedule rules!');
        $this->command->info('Schedule: Monday-Friday (8:00-10:30, 14:00-16:30), Saturday (8:00-10:30 only), Sunday (closed)');
    }
}

