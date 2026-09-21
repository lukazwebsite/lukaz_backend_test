<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'id' => 1,
                'title' => 'Nike',
                'slug' => 'nike',
                'icon' => 'brands/nike.png',
                'href' => 'simple-icons:nike',
                'sequance' => 1.0,
                'status' => 1,
                'isActive' => 1,
                'created_at' => '2025-07-13 10:44:38',
                'updated_at' => '2025-07-13 10:44:38'
            ],
            [
                'id' => 2,
                'title' => 'Adidas',
                'slug' => 'adidas',
                'icon' => 'brands/adidas.png',
                'href' => 'https://www.adidas.com',
                'sequance' => 2.0,
                'status' => 1,
                'isActive' => 1,
                'created_at' => '2025-07-13 10:44:38',
                'updated_at' => '2025-07-13 10:44:38'
            ],
            [
                'id' => 3,
                'title' => 'Puma',
                'slug' => 'puma',
                'icon' => 'brands/puma.png',
                'href' => 'https://www.puma.com',
                'sequance' => 3.0,
                'status' => 1,
                'isActive' => 1,
                'created_at' => '2025-07-13 10:44:38',
                'updated_at' => '2025-07-13 10:44:38'
            ]
        ];
        DB::table('brands')->insert($brands);
    }
}
