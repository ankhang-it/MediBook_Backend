<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get appointments with their patients
        $appointments = DB::table('appointments')
            ->join('patient_profiles', 'appointments.patient_id', '=', 'patient_profiles.patient_id')
            ->join('users', 'patient_profiles.user_id', '=', 'users.user_id')
            ->select('appointments.*', 'users.user_id as patient_user_id')
            ->get();

        $paymentDescriptions = [
            'Phí khám bệnh - Tim mạch',
            'Phí khám bệnh - Nội khoa',
            'Phí khám bệnh - Ngoại khoa',
            'Phí khám bệnh - Sản phụ khoa',
            'Phí khám bệnh - Nhi khoa',
            'Phí khám bệnh - Thần kinh',
            'Phí khám bệnh - Da liễu',
            'Phí khám bệnh - Mắt',
            'Phí khám bệnh - Tai mũi họng',
            'Phí khám bệnh - Xương khớp'
        ];

        $paymentStatuses = ['pending', 'completed', 'failed', 'cancelled'];
        $amounts = [200000, 300000, 400000, 500000, 600000, 800000, 1000000];

        $payments = [];
        $orderId = 1000;

        foreach ($appointments as $appointment) {
            $status = $paymentStatuses[array_rand($paymentStatuses)];
            $amount = $amounts[array_rand($amounts)];
            $description = $paymentDescriptions[array_rand($paymentDescriptions)];

            // If appointment is paid, payment should be completed
            if ($appointment->payment_status === 'paid') {
                $status = 'completed';
            } elseif ($appointment->payment_status === 'unpaid') {
                $status = rand(0, 1) ? 'pending' : 'failed';
            } elseif ($appointment->payment_status === 'refunded') {
                $status = 'cancelled';
            }

            $payments[] = [
                'payment_id' => Str::uuid(),
                'order_id' => $orderId++,
                'total_amount' => $amount,
                'status' => $status,
                'description' => $description,
                'user_id' => $appointment->patient_user_id,
                'appointment_id' => $appointment->appointment_id,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        }

        DB::table('payments')->insert($payments);
    }
}
