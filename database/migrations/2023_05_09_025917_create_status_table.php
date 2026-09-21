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
        Schema::create('status', function (Blueprint $table) {
            $table->id();

            $table->string('unique_id', 100);
            $table->string('name', 255);
            $table->string('slug', 255);

            $table->double('sequence')->nullable()->default(0);

            $table->string('icon', 255)->nullable();
            $table->string('thumbnails', 255)->nullable();
            $table->string('banner', 255)->nullable();
            $table->string('icon_path',255)->nullable();
            $table->string('banner_path',255)->nullable();
            $table->string('thumbnails_path',255)->nullable();

            $table->text('description')->nullable();
            $table->integer('status')->nullable()->default(1)->comment('1 = Active, 0 = Inactive');

            $table->longText('extend_props')->nullable();
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
        Schema::dropIfExists('status');
    }
};
