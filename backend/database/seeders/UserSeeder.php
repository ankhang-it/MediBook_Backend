<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            // Admin user
            [
                'username' => 'admin',
                'email' => 'admin@medibook.com',
                'password' => Hash::make('123456'),
                'phone' => '0123456789',
                'avatar' => null,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Doctor users
            [
                'username' => 'dr_nguyen_van_a',
                'email' => 'dr.nguyenvana@medibook.com',
                'password' => Hash::make('123456'),
                'phone' => '0123456780',
                'avatar' => null,
                'role' => 'doctor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'dr_tran_thi_b',
                'email' => 'dr.tranthib@medibook.com',
                'password' => Hash::make('123456'),
                'phone' => '0123456781',
                'avatar' => 'https://hthaostudio.com/wp-content/uploads/2022/03/Anh-bac-si-nam-7-min.jpg.webp',
                'role' => 'doctor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'dr_le_van_c',
                'email' => 'dr.levanc@medibook.com',
                'password' => Hash::make('123456'),
                'phone' => '0123456782',
                'avatar' => 'https://hthaostudio.com/wp-content/uploads/2022/03/Anh-bac-si-nam-7-min.jpg.webp',
                'role' => 'doctor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'dr_pham_thi_d',
                'email' => 'dr.phamthid@medibook.com',
                'password' => Hash::make('123456'),
                'phone' => '0123456783',
                'avatar' => 'https://hthaostudio.com/wp-content/uploads/2022/03/Anh-bac-si-nam-7-min.jpg.webp',
                'role' => 'doctor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'dr_hoang_van_e',
                'email' => 'dr.hoangvane@medibook.com',
                'password' => Hash::make('123456'),
                'phone' => '0123456784',
                'avatar' => 'https://hthaostudio.com/wp-content/uploads/2022/03/Anh-bac-si-nam-7-min.jpg.webp',
                'role' => 'doctor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Patient users
            [
                'username' => 'patient_001',
                'email' => 'patient001@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654321',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'patient_002',
                'email' => 'patient002@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654322',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'patient_003',
                'email' => 'patient003@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654323',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'patient_004',
                'email' => 'patient004@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654324',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'patient_005',
                'email' => 'patient005@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654325',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'patient_006',
                'email' => 'patient006@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654326',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'patient_007',
                'email' => 'patient007@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654327',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'patient_008',
                'email' => 'patient008@example.com',
                'password' => Hash::make('123456'),
                'phone' => '0987654328',
                'avatar' => null,
                'role' => 'patient',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('users')->insert($users);
    }
}
