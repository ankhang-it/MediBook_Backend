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
        // Get all doctors
        $doctors = DoctorProfile::all();

        foreach ($doctors as $doctor) {
            // Generate time slots for the next 7 days
            TimeSlot::generateSlotsForDoctor($doctor->doctor_id);
        }

        $this->command->info('Time slots regenerated successfully!');
        $this->command->info('Schedule: Monday to Saturday Morning (8:00-10:30), Monday to Friday Afternoon (14:00-16:30)');
    }
}
