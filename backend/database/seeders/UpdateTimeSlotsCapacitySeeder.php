<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TimeSlot;
use App\Models\Appointment;

class UpdateTimeSlotsCapacitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update all existing time slots with default capacity
        TimeSlot::whereNull('max_capacity')->update([
            'max_capacity' => 5,
            'current_bookings' => 0
        ]);

        // Recalculate current_bookings based on existing appointments
        $timeSlots = TimeSlot::all();
        
        foreach ($timeSlots as $timeSlot) {
            $activeAppointments = Appointment::where('time_slot_id', $timeSlot->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();
            
            $timeSlot->update([
                'current_bookings' => $activeAppointments,
                'is_available' => $activeAppointments < $timeSlot->max_capacity
            ]);
        }
    }
}
