<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get patient and doctor IDs
        $patients = DB::table('patient_profiles')->pluck('patient_id');
        $doctors = DB::table('doctor_profiles')->pluck('doctor_id');

        $appointments = [];
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        $paymentStatuses = ['unpaid', 'paid', 'refunded'];

        // Create appointments for the past 3 months and next 2 months
        for ($i = 0; $i < 50; $i++) {
            $patientId = $patients->random();
            $doctorId = $doctors->random();

            // Random date between 3 months ago and 2 months from now
            $randomDays = rand(-90, 60);
            $appointmentDate = Carbon::now()->addDays($randomDays);

            // Random time between 8:00 and 17:00
            $hour = rand(8, 17);
            $minute = rand(0, 1) * 30; // 0 or 30 minutes
            $appointmentDate->setTime($hour, $minute);

            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // If appointment is in the past, it's more likely to be completed
            if ($appointmentDate->isPast()) {
                $status = rand(0, 1) ? 'completed' : 'cancelled';
                $paymentStatus = $status === 'completed' ? 'paid' : 'unpaid';
            }

            $appointments[] = [
                'appointment_id' => Str::uuid(),
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'schedule_time' => $appointmentDate,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 10)),
            ];
        }

        DB::table('appointments')->insert($appointments);
    }
}
