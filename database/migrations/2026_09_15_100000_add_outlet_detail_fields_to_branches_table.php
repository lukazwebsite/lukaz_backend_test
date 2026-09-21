<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('name_bn', 255)->nullable()->after('name');
            $table->string('manager_phone', 20)->nullable()->after('contact');
            $table->text('address_bn')->nullable()->after('address');
            $table->string('map_link', 500)->nullable()->after('address_bn');
            $table->text('map_embed')->nullable()->after('map_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'name_bn',
                'manager_phone',
                'address_bn',
                'map_link',
                'map_embed',
            ]);
        });
    }
};
