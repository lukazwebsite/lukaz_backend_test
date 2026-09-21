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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 100)->index();
            $table->integer('branch_id')->nullable()->index();
            $table->integer('brand_id')->index();
            $table->integer('item_id')->index();
            $table->string('item_name', 255);
            $table->string('icon', 255)->nullable();
            $table->string('slug', 255);
            $table->string('color', 255);
            $table->string('size', 255);
            $table->double('regular_price');
            $table->double('current_price');
            $table->double('discount_amount');
            $table->integer('quantity');
            $table->double('grand_total');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

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
        Schema::dropIfExists('order_items');
    }
};
