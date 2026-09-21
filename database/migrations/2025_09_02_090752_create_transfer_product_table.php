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
        Schema::create('transfer_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('from_branch_id')->index();
            $table->integer('to_branch_id')->index();
            $table->integer('product_id')->index();
            $table->integer('stock_id')->index();
            $table->string('sku', 100)->nullable();
            $table->string('color', 100)->nullable();
            $table->string('size', 100)->nullable();
            $table->string('avaiable_stock', 100)->nullable();
            $table->string('transfer_request', 100)->nullable();
            $table->integer('status')->nullable()->default(1)->comment('1 = Request, 2 = Send, 3 = Approved, 4 = Received, 5 = Cancel');
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
        Schema::dropIfExists('transfer_stocks');
    }
};
