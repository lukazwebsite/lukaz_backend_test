<?php

namespace Database\Seeders;

use App\Models\Api\Notice;
use Illuminate\Database\Seeder;


class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        $notices = [
            [
                'title' => 'Free delivery on orders over £50',
                'description' => 'We offer free UK standard delivery on orders that exceed £50.',
                'href' => 'https://example.com/free-delivery',
                'button_text' => 'Shop Now'
            ],
            [
                'title' => 'Summer Sale: Up to 40% off !',
                'description' => 'Enjoy big savings on selected styles until the end of this month.',
                'href' => 'https://example.com/summer-sale',
                'button_text' => 'Shop Now'
            ],
            [
                'title' => 'New arrivals just dropped!',
                'description' => 'Check out the latest trends and newest pieces added to our store.',
                'href' => 'https://example.com/new-arrivals',
                'button_text' => 'Explore Now'
            ],
        ];

        foreach ($notices as $notice) {
            Notice::create($notice);
        }
    }
}
