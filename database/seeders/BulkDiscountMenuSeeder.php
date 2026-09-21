<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BulkDiscountMenuSeeder extends Seeder
{
    /**
     * Add the Bulk Discount sidebar link and its View/Add/Edit/Delete actions.
     *
     * The menu id is derived from the current table rather than hardcoded, so
     * this runs safely on any database whatever ids already exist. Re-running
     * it is a no-op.
     *
     * @return void
     */
    public function run()
    {
        $existing = DB::table('menus')->where('href', '/bulk_discount')->value('id');

        if ($existing) {
            $menuId = $existing;
        } else {
            $menuId = (int) DB::table('menus')->max('id') + 1;

            DB::table('menus')->insert([
                'id' => $menuId,
                'parent_id' => null,
                'title' => 'Bulk Discount',
                'href' => '/bulk_discount',
                'icon' => 'mdi:sale',
                'sequance' => 14.2,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 1 = View, 2 = Add, 3 = Edit, 4 = Delete
        foreach ([1, 2, 3, 4] as $actionId) {
            $alreadyThere = DB::table('action_menus')
                ->where('menu_id', $menuId)
                ->where('action_id', $actionId)
                ->exists();

            if ($alreadyThere) {
                continue;
            }

            DB::table('action_menus')->insert([
                'menu_id' => $menuId,
                'action_id' => $actionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
