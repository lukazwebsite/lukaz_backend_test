<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actions = array(
            array('id' => '1','parent_id' => NULL,'title' => 'Dashboard','href' => '/dashboard','icon' => 'mdi:home','sequance' => '1','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '2','parent_id' => NULL,'title' => 'Staff','href' => '/staff','icon' => 'fa-solid:users','sequance' => '2','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '3','parent_id' => NULL,'title' => 'Customer','href' => '/customer','icon' => 'raphael:users','sequance' => '3','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '4','parent_id' => NULL,'title' => 'Products','href' => '/product','icon' => 'lucide:boxes','sequance' => '4','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '5','parent_id' => NULL,'title' => 'Stock Transfer','href' => '/stock_transfer','icon' => 'material-symbols:delivery-truck-speed','sequance' => '5','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '6','parent_id' => NULL,'title' => 'Branch','href' => '/branches','icon' => 'ph:warehouse-fill','sequance' => '6','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '7','parent_id' => NULL,'title' => 'Inventory','href' => '/inventory','icon' => 'game-icons:warehouse','sequance' => '7','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '8','parent_id' => NULL,'title' => 'Brand','href' => '/brands','icon' => 'fluent:tag-multiple-16-filled','sequance' => '8','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '9','parent_id' => NULL,'title' => 'Category','href' => '/categories','icon' => 'ri:database-fill','sequance' => '9','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '10','parent_id' => NULL,'title' => 'Pos','href' => '/pos','icon' => 'jam:computer-f','sequance' => '1.1','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '11','parent_id' => NULL,'title' => 'Order Manage','href' => '/order','icon' => 'streamline-ultimate:shopping-cart-full-bold','sequance' => '11','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '12','parent_id' => NULL,'title' => 'Page','href' => '/page','icon' => 'ic:baseline-library-books','sequance' => '12','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '13','parent_id' => NULL,'title' => 'Permission','href' => '/permission','icon' => 'mdi:shield-key','sequance' => '13','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '14','parent_id' => NULL,'title' => 'Courier Charge','href' => '/courier','icon' => 'mdi:courier-fast','sequance' => '14','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '15','parent_id' => '13','title' => 'Role','href' => '/role','icon' => 'fa6-solid:users-gear','sequance' => '14','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '16','parent_id' => '13','title' => 'User Access','href' => '/user_access','icon' => 'tdesign:user-unlocked-filled','sequance' => '16','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '17','parent_id' => '13','title' => 'Role Access','href' => '/role_access','icon' => 'fa6-solid:users-gear','sequance' => '17','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '18','parent_id' => '11','title' => 'New Orders','href' => '/orders','icon' => 'streamline-ultimate:shopping-cart-full-bold','sequance' => '18','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '19','parent_id' => '11','title' => 'Confirm Orders','href' => '/orders/confirm','icon' => 'streamline:shopping-cart-check-solid','sequance' => '19','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '20','parent_id' => '11','title' => 'Shipped Order','href' => '/orders/shipped','icon' => 'fluent:vehicle-truck-checkmark-48-filled','sequance' => '20','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '21','parent_id' => '11','title' => 'Cancel Order','href' => '/orders/cancel','icon' => 'bi:cart-x-fill','sequance' => '21','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '22','parent_id' => '11','title' => 'Delivered Order','href' => '/orders/delivery','icon' => 'mdi:cart-arrow-right','sequance' => '22','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '23','parent_id' => NULL,'title' => 'Notice','href' => '/notices','icon' => 'fe:notice-active','sequance' => '3.1','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '24','parent_id' => NULL,'title' => 'Coupon','href' => '/coupon','icon' => 'bxs:coupon','sequance' => '14.1','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '25','parent_id' => NULL,'title' => 'Video Feature','href' => '/video/feature','icon' => 'bxs:video','sequance' => '15','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '26','parent_id' => NULL,'title' => 'Contact','href' => '/contact','icon' => 'bxs:contact','sequance' => '16','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '27','parent_id' => NULL,'title' => 'Subscribe','href' => '/subscribe','icon' => 'fluent:mail-alert-28-filled','sequance' => '17','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51'),
            array('id' => '28','parent_id' => NULL,'title' => 'Banner','href' => '/banner','icon' => 'ph:flag-banner-fold-fill','sequance' => '18','status' => '1','created_at' => '2023-02-19 12:55:51','updated_at' => '2023-02-19 12:55:51')
        );

        DB::table('menus')->insert($actions);
    }
}
