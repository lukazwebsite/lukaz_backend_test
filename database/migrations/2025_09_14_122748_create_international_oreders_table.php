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
        Schema::create('international_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no', 255)->nullable()->index();
            $table->string('payment_method', 255)->nullable();

            $table->string('icon', 255)->nullable();
            $table->integer('brand_id')->index()->nullable();
            $table->integer('item_id')->index()->nullable();
            $table->string('item_name', 255)->nullable();
            $table->string('item_slug', 255)->nullable();
            $table->string('color', 255)->nullable();
            $table->string('size', 255)->nullable();

            $table->string('regular_price', 255)->nullable();
            $table->string('current_price', 255)->nullable();
            $table->string('discount_amount', 255)->nullable();

            $table->integer('quantity')->nullable();
            $table->integer('country_id')->nullable()->index();
            $table->string('full_name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('whats_app_no', 255)->nullable()->index();
            $table->string('full_address', 555)->nullable();
            $table->text('note')->nullable();

            $table->text('description')->nullable();
            $table->integer('status')->default(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('international_orders');
    }
};
