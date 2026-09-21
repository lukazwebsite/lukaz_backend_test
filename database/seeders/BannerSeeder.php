<?php

namespace Database\Seeders;

use App\Models\Admin\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run(): void
    {
        Banner::create([
            'title' => 'Discover the Latest Trends',
            'description' => 'Watch our product highlights in action',
            'href' => 'https://lukaz-e-com-xi.vercel.app/',
            'image' => 'banners/H7hzzRxaPNx8OvMJvAMAqEW6Ezvz3d5pOzbDcWoT.gif',
            'sequence' => 1,
        ]);
    }
}
