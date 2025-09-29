<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get patient user IDs (users with role 'patient')
        $patientUsers = DB::table('users')
            ->where('role', 'patient')
            ->orderBy('user_id')
            ->get();

        $patientProfiles = [
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[0]->user_id,
                'fullname' => 'Nguyễn Thị Lan',
                'dob' => '1990-05-15',
                'gender' => 'female',
                'address' => '123 Đường Lê Lợi, Quận 1, TP.HCM',
                'medical_history' => 'Tiền sử dị ứng penicillin, cao huyết áp nhẹ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[1]->user_id,
                'fullname' => 'Trần Văn Minh',
                'dob' => '1985-08-22',
                'gender' => 'male',
                'address' => '456 Đường Nguyễn Huệ, Quận 3, TP.HCM',
                'medical_history' => 'Tiền sử đau dạ dày, không có dị ứng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[2]->user_id,
                'fullname' => 'Lê Thị Hoa',
                'dob' => '1992-12-03',
                'gender' => 'female',
                'address' => '789 Đường Điện Biên Phủ, Quận Bình Thạnh, TP.HCM',
                'medical_history' => 'Tiền sử hen suyễn, dị ứng phấn hoa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[3]->user_id,
                'fullname' => 'Phạm Văn Đức',
                'dob' => '1988-03-18',
                'gender' => 'male',
                'address' => '321 Đường Cách Mạng Tháng 8, Quận 10, TP.HCM',
                'medical_history' => 'Tiền sử tiểu đường type 2, không có dị ứng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[4]->user_id,
                'fullname' => 'Hoàng Thị Mai',
                'dob' => '1995-07-25',
                'gender' => 'female',
                'address' => '654 Đường Võ Văn Tần, Quận 3, TP.HCM',
                'medical_history' => 'Không có tiền sử bệnh lý đặc biệt',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[5]->user_id,
                'fullname' => 'Vũ Văn Tuấn',
                'dob' => '1987-11-12',
                'gender' => 'male',
                'address' => '987 Đường Lý Tự Trọng, Quận 1, TP.HCM',
                'medical_history' => 'Tiền sử viêm xoang mãn tính',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[6]->user_id,
                'fullname' => 'Đặng Thị Linh',
                'dob' => '1993-09-08',
                'gender' => 'female',
                'address' => '147 Đường Nguyễn Thị Minh Khai, Quận 1, TP.HCM',
                'medical_history' => 'Tiền sử thiếu máu, dị ứng hải sản',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'patient_id' => Str::uuid(),
                'user_id' => $patientUsers[7]->user_id,
                'fullname' => 'Bùi Văn Hùng',
                'dob' => '1986-04-30',
                'gender' => 'male',
                'address' => '258 Đường Trần Hưng Đạo, Quận 5, TP.HCM',
                'medical_history' => 'Tiền sử đau lưng, không có dị ứng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('patient_profiles')->insert($patientProfiles);
    }
}
