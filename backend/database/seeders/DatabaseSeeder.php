<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SpecialtySeeder::class,
            UserSeeder::class,
            DoctorProfileSeeder::class,
            PatientProfileSeeder::class,
            AppointmentSeeder::class,
            MedicalRecordSeeder::class,
            PaymentSeeder::class,
            FeedbackSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
