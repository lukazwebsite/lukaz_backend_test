<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = array(
            array('id' => 1,'role_id' => 1,'country_id' => '18','name' => 'Majadul islam','mobile' => NULL,'email' => 'admin@gmail.com','password' => bcrypt('12345678'),'status' => '1','remember_token' => NULL,'description' => NULL,'email_verified_at' => NULL,'created_at' => '2025-07-09 16:20:40','updated_at' => '2025-07-09 16:20:40')
        );
        DB::table('users')->insert($users);
    }
}
