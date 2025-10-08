<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\TimeSlot;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\Appointment;

class BookedAppointmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creating booked appointments with time slots...\n";

        // Get all doctors
        $doctors = DoctorProfile::with('user')->get();
        if ($doctors->isEmpty()) {
            echo "No doctors found. Please run UserSeeder first.\n";
            return;
        }

        // Get all patients
        $patients = PatientProfile::with('user')->get();
        if ($patients->isEmpty()) {
            echo "No patients found. Please run UserSeeder first.\n";
            return;
        }

        $appointmentsCreated = 0;

        foreach ($doctors as $doctor) {
            echo "Processing doctor: {$doctor->fullname}\n";

            // Generate time slots for this doctor for the next 7 days
            TimeSlot::generateSlotsForDoctor($doctor->doctor_id);

            // Get available time slots for the next 7 days
            $timeSlots = TimeSlot::where('doctor_id', $doctor->doctor_id)
                ->where('date', '>=', now()->toDateString())
                ->where('date', '<=', now()->addDays(7)->toDateString())
                ->where('is_available', true)
                ->orderBy('date')
                ->orderBy('start_time')
                ->get();

            if ($timeSlots->isEmpty()) {
                echo "No time slots found for doctor: {$doctor->fullname}\n";
                continue;
            }

            // Book 30-50% of available slots randomly
            $slotsToBook = $timeSlots->random(rand(1, min(5, $timeSlots->count())));

            foreach ($slotsToBook as $slot) {
                // Select random patient
                $patient = $patients->random();

                // Random appointment status (weighted towards confirmed)
                $statusWeights = [
                    'pending' => 20,
                    'confirmed' => 60,
                    'cancelled' => 10,
                    'completed' => 10
                ];

                $status = $this->weightedRandom($statusWeights);

                // Payment status based on appointment status
                $paymentStatus = match ($status) {
                    'completed' => 'paid',
                    'cancelled' => rand(0, 1) ? 'unpaid' : 'refunded',
                    'confirmed' => rand(0, 1) ? 'paid' : 'unpaid',
                    'pending' => 'unpaid',
                    default => 'unpaid'
                };

                // Create appointment
                // $slot->date and $slot->start_time are already Carbon objects due to casts
                $scheduleTime = $slot->date->copy()->setTimeFromTimeString($slot->start_time->format('H:i:s'));

                $appointment = Appointment::create([
                    'appointment_id' => Str::uuid(),
                    'patient_id' => $patient->patient_id,
                    'doctor_id' => $doctor->doctor_id,
                    'time_slot_id' => $slot->id,
                    'schedule_time' => $scheduleTime,
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                ]);

                // Mark time slot as unavailable
                $slot->update(['is_available' => false]);

                $appointmentsCreated++;

                echo "Created appointment: {$appointment->appointment_id} for {$patient->user->username} with {$doctor->fullname} on {$slot->date} {$slot->start_time}\n";
            }
        }

        echo "Successfully created {$appointmentsCreated} booked appointments.\n";
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = rand(1, $total);

        $current = 0;
        foreach ($weights as $key => $weight) {
            $current += $weight;
            if ($random <= $current) {
                return $key;
            }
        }

        return array_key_first($weights);
    }
}
