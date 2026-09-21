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
        Schema::create('product_additionals', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->index();
            $table->string('additional_key', 255)->nullable()->index();

            $table->string('slug', 255)->nullable()->index();
            $table->string('color', 255)->nullable()->index();
            $table->string('size', 255)->nullable()->index();

            $table->integer('status')->default(1)->nullable()->comment('1 = Active, 0 = Inactive');
            $table->integer('stock_status')->default(1)->nullable()->comment('1 = Stock, 0 = Stock Out');

            $table->double('regular_price')->nullable()->default(0);
            $table->double('current_price')->nullable()->default(0);

            $table->double('discount')->nullable()->default(0);
            $table->string('discount_type')->nullable()->comment('1 = Fixed, 0 = Parcentage');
            $table->double('stock')->nullable()->default(0);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();

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
        Schema::dropIfExists('product_additionals');
    }
};
