<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MedicalRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get completed appointments
        $completedAppointments = DB::table('appointments')
            ->where('status', 'completed')
            ->get();

        $diagnoses = [
            'Cảm cúm thông thường',
            'Viêm họng cấp',
            'Cao huyết áp',
            'Tiểu đường type 2',
            'Viêm dạ dày',
            'Đau đầu căng thẳng',
            'Viêm xoang',
            'Dị ứng thời tiết',
            'Đau lưng cơ học',
            'Thiếu máu nhẹ',
            'Rối loạn tiêu hóa',
            'Mất ngủ',
            'Viêm phế quản',
            'Đau khớp gối',
            'Căng thẳng, lo âu'
        ];

        $prescriptions = [
            'Paracetamol 500mg x 2 viên/ngày, uống sau ăn',
            'Amoxicillin 500mg x 3 viên/ngày, uống trước ăn 1 giờ',
            'Lisinopril 10mg x 1 viên/ngày, uống buổi sáng',
            'Metformin 500mg x 2 viên/ngày, uống sau ăn',
            'Omeprazole 20mg x 1 viên/ngày, uống trước ăn 30 phút',
            'Ibuprofen 400mg x 3 viên/ngày khi đau',
            'Cetirizine 10mg x 1 viên/ngày, uống buổi tối',
            'Diclofenac gel bôi 2-3 lần/ngày',
            'Vitamin B12 1000mcg x 1 viên/ngày',
            'Probiotic x 2 viên/ngày, uống sau ăn',
            'Melatonin 3mg x 1 viên trước khi ngủ',
            'Salbutamol xịt khi khó thở',
            'Glucosamine 1500mg x 1 viên/ngày',
            'Diazepam 5mg x 1 viên khi cần thiết'
        ];

        $notes = [
            'Bệnh nhân cần nghỉ ngơi, uống nhiều nước',
            'Tái khám sau 1 tuần nếu triệu chứng không cải thiện',
            'Theo dõi huyết áp hàng ngày',
            'Kiểm tra đường huyết định kỳ',
            'Tránh thức ăn cay, nóng',
            'Tập thể dục nhẹ nhàng',
            'Tránh tiếp xúc với chất gây dị ứng',
            'Vật lý trị liệu 2-3 lần/tuần',
            'Bổ sung sắt và vitamin C',
            'Ăn uống đầy đủ chất dinh dưỡng',
            'Tạo thói quen ngủ đúng giờ',
            'Tránh khói thuốc lá',
            'Giảm cân nếu cần thiết',
            'Thư giãn, tránh stress'
        ];

        $medicalRecords = [];

        foreach ($completedAppointments as $appointment) {
            $diagnosis = $diagnoses[array_rand($diagnoses)];
            $prescription = $prescriptions[array_rand($prescriptions)];
            $note = $notes[array_rand($notes)];

            $medicalRecords[] = [
                'record_id' => Str::uuid(),
                'appointment_id' => $appointment->appointment_id,
                'doctor_id' => $appointment->doctor_id,
                'diagnosis' => $diagnosis,
                'prescription' => $prescription,
                'notes' => $note,
                'created_at' => $appointment->created_at,
                'updated_at' => $appointment->updated_at,
            ];
        }

        DB::table('medical_records')->insert($medicalRecords);
    }
}
