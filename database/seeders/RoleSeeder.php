<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = array(
            array('id' => '1','name' => 'Super Admin', 'status' => 2, 'created_at' => NULL,'updated_at' => NULL),
            array('id' => '2','name' => 'Admin', 'status' => 2, 'created_at' => NULL,'updated_at' => NULL),
            array('id' => '3','name' => 'Shop Manger', 'status' => 2, 'created_at' => NULL,'updated_at' => NULL),
            array('id' => '4','name' => 'Staff', 'status' => 2, 'created_at' => NULL,'updated_at' => NULL),
            array('id' => '5','name' => 'Customer','status' => 2, 'created_at' => NULL,'updated_at' => NULL),
            array('id' => '6','name' => 'Vendor','status' => 2, 'created_at' => NULL,'updated_at' => NULL)
        );

        DB::table('roles')->insert($roles);
    }
}
