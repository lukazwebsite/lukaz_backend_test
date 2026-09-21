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
        Schema::table('banners', function (Blueprint $table) {
             $table->integer('status')
                ->default(1)
                ->nullable()
                ->comment('1 = Active, 0 = Inactive')
                ->after('sequence');

            $table->integer('created_by')->nullable()->after('status');
            $table->integer('updated_by')->nullable()->after('created_by');
            $table->dropColumn(['deleted_at','href']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
             $table->string('href', 255)->nullable();
             $table->softDeletes();
             $table->dropColumn(['status', 'created_by', 'updated_by']);
        });
    }
};
