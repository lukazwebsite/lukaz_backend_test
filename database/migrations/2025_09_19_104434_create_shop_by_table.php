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
         Schema::create('shop_by', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('categories')->nullable();
            $table->string('thumbnail')->nullable();
            $table->integer('status')->default(1)->nullable()->comment('1 = Active, 0 = Inactive');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('shop_by');
    }
};
