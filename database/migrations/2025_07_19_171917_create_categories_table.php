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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable()->unsigned();
            $table->string('name', 255);
            $table->string('slug', 255)->nullable();
            $table->string('icon', 255)->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('banner', 255)->nullable();
            $table->double('sequence')->nullable();
            $table->text('description')->nullable();
            $table->integer('status')->default(1);
            $table->integer('isActive')->default(1);
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
        Schema::dropIfExists('categories');
    }
};
