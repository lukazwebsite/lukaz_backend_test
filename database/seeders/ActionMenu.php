<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionMenu extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actions = array(
            array('id' => '1','menu_id' => '2','action_id' => '1','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38'),
            array('id' => '2','menu_id' => '2','action_id' => '2','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38'),
            array('id' => '3','menu_id' => '2','action_id' => '3','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38'),
            array('id' => '4','menu_id' => '2','action_id' => '4','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38'),
            array('id' => '5','menu_id' => '3','action_id' => '1','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38'),
            array('id' => '6','menu_id' => '3','action_id' => '2','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38'),
            array('id' => '7','menu_id' => '3','action_id' => '3','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38'),
            array('id' => '8','menu_id' => '3','action_id' => '4','created_at' => '2025-07-13 10:44:38','updated_at' => '2025-07-13 10:44:38')
        );

        DB::table('action_menus')->insert($actions);
    }
}
