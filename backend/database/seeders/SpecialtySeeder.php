<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
                'description' => 'Chuyên khoa về tim và hệ tuần hoàn',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Nội khoa',
                'description' => 'Chuyên khoa nội tổng hợp',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Ngoại khoa',
                'description' => 'Chuyên khoa phẫu thuật',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Sản phụ khoa',
                'description' => 'Chuyên khoa sản và phụ khoa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Nhi khoa',
                'description' => 'Chuyên khoa trẻ em',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Thần kinh',
                'description' => 'Chuyên khoa thần kinh và não bộ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Da liễu',
                'description' => 'Chuyên khoa da và các bệnh về da',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Mắt',
                'description' => 'Chuyên khoa mắt và thị lực',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Tai mũi họng',
                'description' => 'Chuyên khoa tai mũi họng',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'specialty_id' => Str::uuid(),
                'name' => 'Xương khớp',
                'description' => 'Chuyên khoa xương khớp và cột sống',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('specialties')->insert($specialties);
    }
}
