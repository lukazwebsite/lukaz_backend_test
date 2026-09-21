<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Menu id 43 drives TeamMemberController::$menuId and the sidebar link.
     * Without these rows the link never renders and every role except role_id 1
     * lands on the Unauthorize page.
     */
    private $menuId = 43;

    public function up(): void
    {
        DB::table('menus')->updateOrInsert(
            ['id' => $this->menuId],
            [
                'parent_id' => null,
                'title' => 'Team Member',
                'href' => '/team_member',
                'icon' => 'mdi:account-group',
                'sequance' => 19,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ([1, 2, 3, 4] as $actionId) {
            DB::table('action_menus')->updateOrInsert(
                ['menu_id' => $this->menuId, 'action_id' => $actionId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('action_menus')->where('menu_id', $this->menuId)->delete();
        DB::table('menus')->where('id', $this->menuId)->delete();
    }
};
