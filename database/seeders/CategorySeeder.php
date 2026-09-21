<?php

namespace Database\Seeders;

use App\Models\Admin\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Root categories
        $electronics = Category::create([
            'title' => 'Electronics',
            'slug' => 'electronics',
            'icon' => 'fas fa-tv',
            'href' => '/electronics',
            'sequence' => 1,
            'status' => 1,
            'isActive' => 1,
        ]);

        $fashion = Category::create([
            'title' => 'Fashion',
            'slug' => 'fashion',
            'icon' => 'fas fa-tshirt',
            'href' => '/fashion',
            'sequence' => 2,
            'status' => 1,
            'isActive' => 1,
        ]);

        // Subcategories (child of electronics)
        Category::create([
            'title' => 'Mobile Phones',
            'slug' => 'mobile-phones',
            'icon' => 'fas fa-mobile-alt',
            'href' => '/electronics/mobiles',
            'sequence' => 1.1,
            'status' => 1,
            'isActive' => 1,
            'parent_id' => $electronics->id,
        ]);

        Category::create([
            'title' => 'Laptops',
            'slug' => 'laptops',
            'icon' => 'fas fa-laptop',
            'href' => '/electronics/laptops',
            'sequence' => 1.2,
            'status' => 1,
            'isActive' => 1,
            'parent_id' => $electronics->id,
        ]);

        // Subcategories (child of fashion)
        Category::create([
            'title' => 'Men\'s Clothing',
            'slug' => 'mens-clothing',
            'icon' => 'fas fa-male',
            'href' => '/fashion/men',
            'sequence' => 2.1,
            'status' => 1,
            'isActive' => 1,
            'parent_id' => $fashion->id,
        ]);

        Category::create([
            'title' => 'Women\'s Clothing',
            'slug' => 'womens-clothing',
            'icon' => 'fas fa-female',
            'href' => '/fashion/women',
            'sequence' => 2.2,
            'status' => 1,
            'isActive' => 1,
            'parent_id' => $fashion->id,
        ]);
    }
}
