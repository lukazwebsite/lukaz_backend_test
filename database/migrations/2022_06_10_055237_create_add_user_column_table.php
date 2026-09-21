<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 15)->nullable()->index()->unique();
            $table->integer('status')->default(1)->comment('1 = Active, 0 = Inactive');
            $table->text('description')->nullable();
            $table->date('joining')->nullable();
            $table->integer('role_id')->default(5)->comment('1 = Admin, 2 = User, 5 = Custommer')->after('id')->index();
            $table->integer('branch_id')->nullable()->index();
            $table->integer('country_id')->after('role_id');
            $table->integer('created_by')->after('email_verified_at	')->nullable();
            $table->integer('updated_by')->after('created_by')->nullable();;

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('mobile');
            $table->dropColumn('description');
            $table->dropColumn('role_id');
            $table->dropColumn('country_id');

        });
    }
};
