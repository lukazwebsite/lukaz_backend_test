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
        Schema::create('bulk_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);

            $table->string('target_type', 20)->index()->comment('category = category wide, product = hand picked');
            $table->integer('priority')->default(1)->index()->comment('1 = category, 2 = product. Higher wins.');

            $table->string('discount_type')->nullable()->comment('1 = Fixed, 0 = Parcentage');
            $table->double('discount')->nullable()->default(0);

            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();

            $table->integer('status')->default(1)->index()->comment('1 = Active, 0 = Inactive');

            $table->text('description')->nullable();

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('bulk_discount_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulk_discount_id')->index();
            $table->unsignedBigInteger('category_id')->index();
            $table->timestamps();

            $table->unique(['bulk_discount_id', 'category_id'], 'bulk_discount_category_unique');
        });

        Schema::create('bulk_discount_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bulk_discount_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->timestamps();

            $table->unique(['bulk_discount_id', 'product_id'], 'bulk_discount_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_discount_products');
        Schema::dropIfExists('bulk_discount_categories');
        Schema::dropIfExists('bulk_discounts');
    }
};
