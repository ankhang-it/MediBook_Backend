<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;
use Illuminate\Support\Str;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialties = [
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Tim mạch',
                'description' => 'Khoa chuyên điều trị các bệnh lý về tim mạch và huyết áp'
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Thần kinh',
                'description' => 'Khoa chuyên điều trị các bệnh lý về hệ thần kinh'
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Nhi khoa',
                'description' => 'Khoa chuyên chăm sóc sức khỏe trẻ em từ sơ sinh đến 18 tuổi'
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Da liễu',
                'description' => 'Khoa chuyên điều trị các bệnh lý về da và thẩm mỹ da'
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Nội khoa',
                'description' => 'Khoa chuyên điều trị các bệnh lý nội khoa tổng quát'
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Ngoại khoa',
                'description' => 'Khoa chuyên thực hiện các ca phẫu thuật và điều trị ngoại khoa'
            ]
        ];

        foreach ($specialties as $specialty) {
            Specialty::create($specialty);
        }
    }
}
