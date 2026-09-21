<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actions = array(
            array('id' => '1','name' => 'View','created_at' => NULL,'updated_at' => NULL),
            array('id' => '2','name' => 'Add','created_at' => NULL,'updated_at' => NULL),
            array('id' => '3','name' => 'Edit','created_at' => NULL,'updated_at' => NULL),
            array('id' => '4','name' => 'Delete','created_at' => NULL,'updated_at' => NULL),
            array('id' => '5','name' => 'Export','created_at' => NULL,'updated_at' => NULL),
            array('id' => '6','name' => 'Import','created_at' => NULL,'updated_at' => NULL)
          );

        DB::table('actions')->insert($actions);
    }
}
