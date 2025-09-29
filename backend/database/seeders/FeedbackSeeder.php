<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get completed appointments with their patients and doctors
        $completedAppointments = DB::table('appointments')
            ->join('patient_profiles', 'appointments.patient_id', '=', 'patient_profiles.patient_id')
            ->join('doctor_profiles', 'appointments.doctor_id', '=', 'doctor_profiles.doctor_id')
            ->where('appointments.status', 'completed')
            ->select('appointments.*', 'patient_profiles.patient_id', 'doctor_profiles.doctor_id')
            ->get();

        $comments = [
            'Bác sĩ rất tận tâm và chuyên nghiệp',
            'Thời gian khám bệnh hợp lý, không phải chờ đợi lâu',
            'Bác sĩ giải thích rõ ràng về tình trạng bệnh',
            'Dịch vụ tốt, sẽ quay lại khám tiếp',
            'Bác sĩ có kinh nghiệm, chẩn đoán chính xác',
            'Phòng khám sạch sẽ, nhân viên thân thiện',
            'Bác sĩ tư vấn chi tiết về cách điều trị',
            'Rất hài lòng với chất lượng dịch vụ',
            'Bác sĩ kiên nhẫn lắng nghe bệnh nhân',
            'Điều trị hiệu quả, bệnh tình cải thiện rõ rệt',
            'Bác sĩ có thái độ tốt, tạo cảm giác thoải mái',
            'Quy trình khám bệnh nhanh chóng, tiện lợi',
            'Bác sĩ chuyên môn cao, đáng tin cậy',
            'Dịch vụ chăm sóc bệnh nhân tốt',
            'Bác sĩ tư vấn về chế độ ăn uống hợp lý'
        ];

        $feedbacks = [];

        // Only create feedback for 70% of completed appointments
        $appointmentsToFeedback = $completedAppointments->random(ceil($completedAppointments->count() * 0.7));

        foreach ($appointmentsToFeedback as $appointment) {
            $rating = rand(3, 5); // Most feedbacks are positive (3-5 stars)
            $comment = $comments[array_rand($comments)];

            $feedbacks[] = [
                'feedback_id' => Str::uuid(),
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 10)),
            ];
        }

        DB::table('feedback')->insert($feedbacks);
    }
}
