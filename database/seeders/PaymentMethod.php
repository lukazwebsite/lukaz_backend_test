<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethod extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actions = array(
            array('id' => '1','name' => 'Cash On Delivery','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '2','name' => 'Bkash Pay','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '3','name' => 'SSL Commerze','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '4','name' => 'Surjo Pay','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '5','name' => 'Stripe Pay','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '6','name' => 'Pay Pal','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '7','name' => 'Google Pay','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '8','name' => 'promo','description' => "NULL",'status' => '0','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            array('id' => '9','name' => 'Blance','description' => "NULL",'status' => '1','created_at' => '2023-07-19 12:55:51','updated_at' => '2023-07-19 12:55:51'),
            
        );

        DB::table('payment_methods')->insert($actions);
    }
}
