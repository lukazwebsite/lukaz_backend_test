<?php

namespace App\Services\Steadfast;

use App\Models\Api\Order;

/**
 * Turns a local order into the payload Steadfast expects.
 *
 * Kept apart from the HTTP client because this is where the money decision
 * lives: whether the rider collects cash, and how much.
 */
class SteadfastOrderMapper
{
    /**
     * @param  Order  $order  with shippingInfo loaded
     * @return array
     */
    public function build(Order $order)
    {
        $shipping = $order->shippingInfo;

        if (!$shipping) {
            throw new SteadfastException('Order ' . $order->order_no . ' has no shipping address.');
        }

        $phone = $this->normalisePhone($shipping->phone);

        if ($phone === null) {
            throw new SteadfastException(
                'Order ' . $order->order_no . ' has an unusable phone number (' . $shipping->phone . ').'
            );
        }

        $address = trim((string) $shipping->address);

        if ($address === '') {
            throw new SteadfastException('Order ' . $order->order_no . ' has an empty address.');
        }

        $payload = [
            'invoice' => $order->order_no,
            'recipient_name' => $this->flatten($shipping->full_name),
            'recipient_phone' => $phone,
            'recipient_address' => $this->fullAddress($shipping),
            'cod_amount' => $this->codAmount($order),
            'note' => $this->flatten($shipping->note),
            'delivery_type' => (int) config('steadfast.delivery_type', 0),
        ];

        if ($payload['recipient_name'] === '') {
            throw new SteadfastException('Order ' . $order->order_no . ' has no recipient name.');
        }

        return $payload;
    }

    /**
     * Cash on delivery amount, decided by payment_status.
     *
     * Both mistakes here cost real money: collecting on an order that is
     * already paid takes the customer's money twice, and shipping an unpaid
     * order at zero gives the goods away. So anything that is not clearly
     * settled or clearly outstanding is refused rather than guessed. A
     * partially paid order is the obvious case, because the balance still
     * owed is not recorded anywhere on the order.
     */
    public function codAmount(Order $order)
    {
        $status = strtolower(trim((string) $order->payment_status));

        $settled = array_map('strtolower', (array) config('steadfast.settled_payment_statuses', []));
        $collect = array_map('strtolower', (array) config('steadfast.collect_payment_statuses', []));

        if (in_array($status, $settled, true)) {
            return 0;
        }

        if (in_array($status, $collect, true)) {
            return round((float) $order->grand_total, 2);
        }

        throw new SteadfastException(
            'Order ' . $order->order_no . ' has payment status "' . $order->payment_status
            . '", so the amount to collect on delivery is unclear. Settle or mark the order first.'
        );
    }

    /**
     * Steadfast wants one address line. Thana and district are separate
     * columns locally, so fold them in; riders need them.
     *
     * Two quirks of the stored data are handled here. Addresses typed into
     * the storefront contain hard line breaks, which do not belong in a
     * single JSON address field. And shipping_info.thana holds a plain id
     * for most rows rather than a name, with no lookup table to resolve it
     * against, so a numeric thana is dropped instead of printing a bare
     * number on the label.
     */
    protected function fullAddress($shipping)
    {
        $parts = [$this->flatten($shipping->address)];

        $thana = $this->flatten($shipping->thana);

        if ($thana !== '' && !ctype_digit($thana)) {
            $parts[] = $thana;
        }

        $district = $this->flatten(optional($shipping->district)->name);

        if ($district !== '') {
            $parts[] = $district;
        }

        $parts = array_values(array_unique(array_filter($parts, function ($part) {
            return $part !== '';
        })));

        return implode(', ', $parts);
    }

    /**
     * Collapse line breaks and repeated whitespace into single spaces.
     */
    protected function flatten($value)
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    }

    /**
     * Steadfast requires exactly 11 digits (01XXXXXXXXX). Strip separators and
     * any 88 / +88 country prefix the storefront may have stored.
     */
    public function normalisePhone($phone)
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        // 8801XXXXXXXXX -> 01XXXXXXXXX
        if (strlen($digits) === 13 && substr($digits, 0, 3) === '880') {
            $digits = substr($digits, 2);
        }

        // 1XXXXXXXXX -> 01XXXXXXXXX
        if (strlen($digits) === 10 && substr($digits, 0, 1) === '1') {
            $digits = '0' . $digits;
        }

        if (strlen($digits) !== 11 || substr($digits, 0, 2) !== '01') {
            return null;
        }

        return $digits;
    }
}
