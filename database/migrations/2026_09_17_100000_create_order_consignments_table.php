<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Courier bookings live in their own table rather than as columns on
     * orders, so a failed attempt can be retried without losing the previous
     * response, and a second courier can be added later without another
     * round of order columns.
     */
    public function up(): void
    {
        Schema::create('order_consignments', function (Blueprint $table) {
            $table->id();

            $table->string('courier', 50)->default('steadfast')->index();
            $table->string('order_no', 100)->index();

            $table->string('invoice', 100);
            $table->string('consignment_id', 100)->nullable()->index();
            $table->string('tracking_code', 100)->nullable()->index();

            $table->double('cod_amount')->default(0);

            // Steadfast delivery status, kept verbatim as the API returns it.
            $table->string('delivery_status', 50)->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();

            $table->longText('request_payload')->nullable();
            $table->longText('response_payload')->nullable();

            $table->integer('created_by')->nullable();
            $table->timestamps();

            // One live booking per order per courier. This is what stops a
            // double click from creating two consignments for one parcel.
            $table->unique(['courier', 'order_no'], 'order_consignments_courier_order_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_consignments');
    }
};
