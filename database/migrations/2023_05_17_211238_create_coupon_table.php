<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255)->nullable();
            $table->integer('discount')->nullable();
            $table->integer('discount_type')->nullable()->comment('1 = fixed, 0 = parcentage');
            $table->integer('limit')->nullable()->default(1)->comment('0 = unlimited');
            $table->integer('max_discount')->nullable()->default(0)->comment('0 = unlimited');
            $table->string('icon', 255)->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('banner', 255)->nullable();
            $table->double('sequance')->nullable();
            $table->timestamp('start_date')->index()->nullable();
            $table->timestamp('end_date')->index()->nullable();
            $table->text('description')->nullable();
            $table->integer('status')->default(1);
            $table->integer('created_by')->default(1);
            $table->integer('updated_by')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('coupons');


    }
};
