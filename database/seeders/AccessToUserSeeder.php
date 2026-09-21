<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessToUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $access_roles = array(
            array('id' => '1','user_id' => '1','menu_id' => '11','action_id' => '1','created_at' => '2023-02-17 18:47:18','updated_at' => '2023-02-17 18:47:18'),
            array('id' => '2','user_id' => '1','menu_id' => '11','action_id' => '2','created_at' => '2023-02-17 18:47:36','updated_at' => '2023-02-17 18:47:36'),
            array('id' => '3','user_id' => '1','menu_id' => '11','action_id' => '3','created_at' => '2023-02-17 18:47:36','updated_at' => '2023-02-17 18:47:36'),
            array('id' => '4','user_id' => '1','menu_id' => '11','action_id' => '4','created_at' => '2023-02-17 18:47:36','updated_at' => '2023-02-17 18:47:36')
        );

        DB::table('access_to_users')->insert($access_roles);
    }
}
