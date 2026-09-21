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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 100)->index();
            $table->integer('user_id')->nullable();

            $table->integer('status')->nullable()->default(1)->comment('1 = Pending');
            $table->integer('order_type')->nullable()->default(1)->comment('1 = Regular, 0 = Pre-order');
            $table->double('discount')->nullable()->default(0);
            $table->integer('quantity')->nullable()->default(0);
            $table->integer('shipping_cost')->nullable()->defalut(0);
            $table->double('total')->nullable()->default(0);
            $table->double('grand_total')->nullable()->default(0);


            $table->string('payment_status', 100);
            $table->string('promo_code', 100)->nullable();

            $table->longText('description')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
