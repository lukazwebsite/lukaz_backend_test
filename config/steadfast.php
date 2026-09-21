<?php

return [

    /*
     * Master switch. When this is false no consignment is ever sent and the
     * admin UI hides the courier actions, so the integration can be parked
     * without removing code.
     */
    'enabled' => env('STEADFAST_ENABLED', false),

    'base_url' => rtrim(env('STEADFAST_BASE_URL', 'https://portal.packzy.com/api/v1'), '/'),

    'api_key' => env('STEADFAST_API_KEY'),
    'secret_key' => env('STEADFAST_SECRET_KEY'),

    'timeout' => (int) env('STEADFAST_TIMEOUT', 30),

    /*
     * 0 = home delivery, 1 = point delivery / return. Home delivery is what
     * the storefront sells, so that is the default.
     */
    'delivery_type' => (int) env('STEADFAST_DELIVERY_TYPE', 0),

    /*
     * Whether the rider collects money is decided by payment_status, not by
     * payment_method. In this database payment_method is null for every
     * online order and holds 'cash' only for POS walk-in sales, which are
     * never couriered. Driving cod off the method would therefore ship every
     * online order with cod_amount 0 and collect nothing.
     *
     * Statuses matched case insensitively against orders.payment_status:
     *   settled -> nothing to collect, cod_amount 0
     *   collect -> rider collects the full grand_total
     * A status in neither list is refused rather than guessed, because both
     * wrong answers cost money.
     */
    'settled_payment_statuses' => ['paid'],
    'collect_payment_statuses' => ['due'],

    /*
     * Steadfast delivery status -> local status table id.
     *
     * Only the ids that already exist in the status table are used here, so
     * the storefront order timeline keeps working without new rows:
     *   5 = Shipped, 6 = Cancel, 8 = Delivered
     *
     * Statuses that map to null are recorded on the consignment but do not
     * move the order, because they are Steadfast side approval states that
     * are not final yet.
     */
    'status_map' => [
        'pending' => 5,
        'in_review' => 5,
        'hold' => 5,
        'delivered_approval_pending' => null,
        'partial_delivered_approval_pending' => null,
        'cancelled_approval_pending' => null,
        'unknown_approval_pending' => null,
        'delivered' => 8,
        'partial_delivered' => 8,
        'cancelled' => 6,
        'unknown' => null,
    ],

    /*
     * Once a consignment reaches one of these the sync command stops polling
     * it.
     */
    'final_statuses' => ['delivered', 'partial_delivered', 'cancelled'],
];
