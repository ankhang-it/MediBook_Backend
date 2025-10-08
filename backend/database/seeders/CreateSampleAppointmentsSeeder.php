<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\PatientProfile;
use App\Models\DoctorProfile;
use App\Models\TimeSlot;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CreateSampleAppointmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first patient and doctor
        $patient = PatientProfile::first();
        $doctor = DoctorProfile::first();
        
        if (!$patient || !$doctor) {
            $this->command->error('No patient or doctor found. Please run other seeders first.');
            return;
        }

        // Create some sample appointments
        $appointments = [
            [
                'appointment_id' => Str::uuid(),
                'patient_id' => $patient->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'schedule_time' => Carbon::now()->addDays(1)->setTime(8, 30), // Tomorrow 8:30 AM
                'status' => 'confirmed',
                'payment_status' => 'paid',
            ],
            [
                'appointment_id' => Str::uuid(),
                'patient_id' => $patient->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'schedule_time' => Carbon::now()->addDays(2)->setTime(14, 0), // Day after tomorrow 2:00 PM
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ],
            [
                'appointment_id' => Str::uuid(),
                'patient_id' => $patient->patient_id,
                'doctor_id' => $doctor->doctor_id,
                'schedule_time' => Carbon::now()->subDays(1)->setTime(9, 0), // Yesterday 9:00 AM (past appointment)
                'status' => 'completed',
                'payment_status' => 'paid',
            ],
        ];

        foreach ($appointments as $appointmentData) {
            // Find or create a time slot for this appointment
            $timeSlot = TimeSlot::where('doctor_id', $doctor->doctor_id)
                ->where('date', $appointmentData['schedule_time']->toDateString())
                ->where('start_time', $appointmentData['schedule_time']->format('H:i:s'))
                ->first();

            if ($timeSlot) {
                $appointmentData['time_slot_id'] = $timeSlot->id;
                // Mark time slot as unavailable if appointment is confirmed or completed
                if (in_array($appointmentData['status'], ['confirmed', 'completed'])) {
                    $timeSlot->update(['is_available' => false]);
                }
            }

            Appointment::create($appointmentData);
        }

        $this->command->info('Sample appointments created successfully!');
        $this->command->info('Created appointments for patient: ' . $patient->fullname);
        $this->command->info('Created appointments with doctor: ' . $doctor->fullname);
    }
}

