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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->index();
            $table->string('slug', 255)->index();
            $table->double('regular_price')->nullable()->default(0);
            $table->double('current_price')->nullable()->default(0);
            $table->string('discount_type')->nullable()->comment('1 = Fixed, 0 = Parcentage');
            $table->double('discount')->nullable()->default(0);
            $table->integer('brand_id')->nullable()->index();
            $table->boolean('status')->nullable()->default(0)->comment('1 = Active, 0 = Inactive');
            $table->json('category_ids')->nullable();
            $table->string('sku')->nullable()->index();
            $table->longText('description')->nullable();

            $table->json('color')->nullable();
            $table->json('size')->nullable();


            $table->integer('payment_type')->nullable()->default(0)->comment('0 = Cash on Delivery, 1 = Partial Payment, 2 = Full Payment');
            $table->integer('partial_amount')->nullable()->default(0);
            $table->integer('delivery_express_support')->nullable()->default(0)->comment('1 = Yes, 0 = No');
            $table->integer('delivery_express')->nullable()->default(0);


            $table->boolean('is_pre_order')->nullable()->default(0)->comment('1 = Yes, 0 = No');
            $table->string('pre_order_days')->nullable();
            $table->string('pre_order_notes')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();

            $table->time('start_time')->nullable()->index();
            $table->time('end_time')->nullable()->index();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->double('special_discount')->nullable()->default(0);

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
