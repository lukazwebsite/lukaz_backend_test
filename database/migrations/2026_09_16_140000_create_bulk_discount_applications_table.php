<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ledger of prices a bulk discount currently owns.
     *
     * Without this the sync cannot tell a campaign discount from a discount an
     * admin typed into the product form, because both live in the same
     * products.current_price / products.discount columns. A row here is the
     * proof that a campaign wrote that price, and it carries the value the
     * product had beforehand so the manual discount can be handed back intact
     * when the campaign ends.
     */
    public function up(): void
    {
        Schema::create('bulk_discount_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('bulk_discount_id')->index();

            // What the campaign wrote. If the live price no longer matches this,
            // somebody edited the product by hand and the campaign must not
            // clobber that edit.
            $table->double('applied_price')->nullable();

            // What the product looked like before the campaign touched it.
            $table->double('original_regular_price')->nullable();
            $table->double('original_current_price')->nullable();
            $table->double('original_discount')->nullable();
            $table->string('original_discount_type')->nullable();

            $table->timestamps();

            $table->unique('product_id', 'bulk_discount_application_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_discount_applications');
    }
};
