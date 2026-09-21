<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $status = array(
            array('id' => 1,'unique_id' => 'K1uPjcy70nApJ62','name' => 'Pending','slug' => 'pending_K1uPjcy70nApJ62','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:22:20','updated_at' => '2023-05-09 03:22:20'),
            array('id' => 2,'unique_id' => 'L1yhz8QZil0fYw1','name' => 'Confirmed','slug' => 'confirmed_L1yhz8QZil0fYw1','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:22:31','updated_at' => '2023-05-09 03:22:31'),
            array('id' => 3,'unique_id' => 'L1yhz8QZil0fYw1','name' => 'Processing','slug' => 'processing_L1yhz8QZil0fYw1','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:22:31','updated_at' => '2023-05-09 03:22:31'),
            array('id' => 4,'unique_id' => 'PntADUTf4eJgCtG','name' => 'Packaging','slug' => 'packaging_PntADUTf4eJgCtG','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:22:41','updated_at' => '2023-05-09 03:22:41'),
            array('id' => 5,'unique_id' => 'Y1dijwGUE0Fo72f','name' => 'Shipped','slug' => 'shipped_Y1dijwGUE0Fo72f','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:22:59','updated_at' => '2023-05-09 03:22:59'),
            array('id' => 6,'unique_id' => 'vca5aHh3ha7ydCi','name' => 'Cancel','slug' => 'cancel_vca5aHh3ha7ydCi','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:23:13','updated_at' => '2023-05-09 03:23:13'),
            array('id' => 7,'unique_id' => 'YJaz1yzZPWfxSCN','name' => 'Rejected','slug' => 'rejected_YJaz1yzZPWfxSCN','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:23:23','updated_at' => '2023-05-09 03:23:23'),
            array('id' => 8,'unique_id' => '1tQJg9a7grO1Yfj','name' => 'Delivered','slug' => 'delivered_1tQJg9a7grO1Yfj','sequence' => '0','icon' => NULL,'thumbnails' => NULL,'banner' => NULL,'icon_path' => NULL,'banner_path' => NULL,'thumbnails_path' => NULL,'description' => NULL,'status' => NULL,'extend_props' => '{"check":"test status"}','created_by' => '1','updated_by' => '1','created_at' => '2023-05-09 03:24:10','updated_at' => '2023-05-09 03:24:10')
        );

        DB::table('status')->insert($status);
    }
}
