<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DoctorProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get specialty IDs
        $specialties = DB::table('specialties')->pluck('specialty_id', 'name');

        // Get doctor user IDs (users with role 'doctor')
        $doctorUsers = DB::table('users')
            ->where('role', 'doctor')
            ->orderBy('user_id')
            ->get();

        $doctorProfiles = [
            [
                'doctor_id' => Str::uuid(),
                'user_id' => $doctorUsers[0]->user_id,
                'fullname' => 'BS. Nguyễn Văn A',
                'specialty_id' => $specialties['Tim mạch'],
                'experience' => '15 năm kinh nghiệm trong lĩnh vực tim mạch. Tốt nghiệp Đại học Y Hà Nội, chuyên khoa Tim mạch. Đã thực hiện hơn 1000 ca phẫu thuật tim.',
                'license_number' => 'BS001234',
                'schedule' => json_encode([
                    'monday' => ['08:00-12:00', '14:00-17:00'],
                    'tuesday' => ['08:00-12:00', '14:00-17:00'],
                    'wednesday' => ['08:00-12:00'],
                    'thursday' => ['08:00-12:00', '14:00-17:00'],
                    'friday' => ['08:00-12:00', '14:00-17:00'],
                    'saturday' => ['08:00-12:00'],
                    'sunday' => []
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => Str::uuid(),
                'user_id' => $doctorUsers[1]->user_id,
                'fullname' => 'BS. Trần Thị B',
                'specialty_id' => $specialties['Nội khoa'],
                'experience' => '12 năm kinh nghiệm trong lĩnh vực nội khoa. Tốt nghiệp Đại học Y TP.HCM, chuyên khoa Nội tổng hợp.',
                'license_number' => 'BS001235',
                'schedule' => json_encode([
                    'monday' => ['08:00-12:00', '14:00-17:00'],
                    'tuesday' => ['08:00-12:00'],
                    'wednesday' => ['08:00-12:00', '14:00-17:00'],
                    'thursday' => ['08:00-12:00', '14:00-17:00'],
                    'friday' => ['08:00-12:00'],
                    'saturday' => ['08:00-12:00', '14:00-17:00'],
                    'sunday' => []
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => Str::uuid(),
                'user_id' => $doctorUsers[2]->user_id,
                'fullname' => 'BS. Lê Văn C',
                'specialty_id' => $specialties['Ngoại khoa'],
                'experience' => '18 năm kinh nghiệm trong lĩnh vực ngoại khoa. Tốt nghiệp Đại học Y Hà Nội, chuyên khoa Ngoại tổng hợp.',
                'license_number' => 'BS001236',
                'schedule' => json_encode([
                    'monday' => ['08:00-12:00'],
                    'tuesday' => ['08:00-12:00', '14:00-17:00'],
                    'wednesday' => ['08:00-12:00', '14:00-17:00'],
                    'thursday' => ['08:00-12:00'],
                    'friday' => ['08:00-12:00', '14:00-17:00'],
                    'saturday' => ['08:00-12:00', '14:00-17:00'],
                    'sunday' => []
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => Str::uuid(),
                'user_id' => $doctorUsers[3]->user_id,
                'fullname' => 'BS. Phạm Thị D',
                'specialty_id' => $specialties['Sản phụ khoa'],
                'experience' => '10 năm kinh nghiệm trong lĩnh vực sản phụ khoa. Tốt nghiệp Đại học Y TP.HCM, chuyên khoa Sản phụ khoa.',
                'license_number' => 'BS001237',
                'schedule' => json_encode([
                    'monday' => ['08:00-12:00', '14:00-17:00'],
                    'tuesday' => ['08:00-12:00', '14:00-17:00'],
                    'wednesday' => ['08:00-12:00', '14:00-17:00'],
                    'thursday' => ['08:00-12:00'],
                    'friday' => ['08:00-12:00', '14:00-17:00'],
                    'saturday' => ['08:00-12:00'],
                    'sunday' => []
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'doctor_id' => Str::uuid(),
                'user_id' => $doctorUsers[4]->user_id,
                'fullname' => 'BS. Hoàng Văn E',
                'specialty_id' => $specialties['Nhi khoa'],
                'experience' => '14 năm kinh nghiệm trong lĩnh vực nhi khoa. Tốt nghiệp Đại học Y Hà Nội, chuyên khoa Nhi.',
                'license_number' => 'BS001238',
                'schedule' => json_encode([
                    'monday' => ['08:00-12:00', '14:00-17:00'],
                    'tuesday' => ['08:00-12:00', '14:00-17:00'],
                    'wednesday' => ['08:00-12:00', '14:00-17:00'],
                    'thursday' => ['08:00-12:00', '14:00-17:00'],
                    'friday' => ['08:00-12:00', '14:00-17:00'],
                    'saturday' => ['08:00-12:00'],
                    'sunday' => ['08:00-12:00']
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('doctor_profiles')->insert($doctorProfiles);
    }
}
