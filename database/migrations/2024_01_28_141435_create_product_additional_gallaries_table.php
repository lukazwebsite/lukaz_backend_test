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
        Schema::create('product_additional_gallaries', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->string('color', 255)->nullable();
            $table->string('slug', 255)->nullable()->index();
            $table->longText('color_icon')->nullable()->index();
            $table->longText('color_icon_small')->nullable()->index();

            $table->longText('color_thumbnails_small')->nullable()->index();
            $table->longText('color_thumbnails')->nullable()->index();

            $table->json('color_galleries_small')->nullable()->index();
            $table->json('color_galleries')->nullable();
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
        Schema::dropIfExists('product_additional_gallaries');
    }
};
