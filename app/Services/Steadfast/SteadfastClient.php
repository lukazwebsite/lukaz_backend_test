<?php

namespace App\Services\Steadfast;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin wrapper over the Steadfast (Packzy) courier API.
 *
 * This class knows about HTTP and nothing about orders. Building a payload
 * from an order is SteadfastOrderMapper's job, so this stays testable with
 * Http::fake() and reusable from both the controller and the sync command.
 */
class SteadfastClient
{
    /**
     * Book a single consignment.
     *
     * @param  array  $payload  as built by SteadfastOrderMapper
     * @return array  the "consignment" object from the reply
     */
    public function createOrder(array $payload)
    {
        $response = $this->request('post', 'create_order', $payload);

        if (!isset($response['consignment'])) {
            throw new SteadfastException(
                'Steadfast accepted the request but returned no consignment.',
                ['response' => $response]
            );
        }

        return $response['consignment'];
    }

    /**
     * Book up to 500 consignments in one call. Steadfast returns a per item
     * result list, so a partial success is normal and the caller must read
     * each entry rather than assuming all of them worked.
     *
     * @param  array  $orders  list of payloads
     * @return array  per item results
     */
    public function createBulk(array $orders)
    {
        if (count($orders) > 500) {
            throw new SteadfastException('Steadfast accepts at most 500 orders per bulk call.');
        }

        $response = $this->request('post', 'create_order/bulk-order', ['data' => $orders]);

        return $response['data'] ?? $response;
    }

    public function statusByConsignment($consignmentId)
    {
        return $this->request('get', 'status_by_cid/' . rawurlencode($consignmentId));
    }

    public function statusByInvoice($invoice)
    {
        return $this->request('get', 'status_by_invoice/' . rawurlencode($invoice));
    }

    public function statusByTrackingCode($trackingCode)
    {
        return $this->request('get', 'status_by_trackingcode/' . rawurlencode($trackingCode));
    }

    public function getBalance()
    {
        return $this->request('get', 'get_balance');
    }

    /**
     * Single exit point for every call, so auth, timeout, retry and logging
     * are applied uniformly and cannot be forgotten by a new method.
     */
    protected function request($method, $path, array $payload = [])
    {
        $apiKey = config('steadfast.api_key');
        $secretKey = config('steadfast.secret_key');

        if (empty($apiKey) || empty($secretKey)) {
            throw new SteadfastException('Steadfast credentials are not configured.');
        }

        $url = config('steadfast.base_url') . '/' . ltrim($path, '/');

        try {
            $request = Http::withHeaders([
                'Api-Key' => $apiKey,
                'Secret-Key' => $secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(config('steadfast.timeout', 30))
                ->retry(2, 500, null, false);

            $response = $method === 'get'
                ? $request->get($url)
                : $request->post($url, $payload);
        } catch (Throwable $e) {
            // Network level failure: DNS, TLS, connection timeout.
            Log::error('Steadfast transport failure', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            throw new SteadfastException('Could not reach Steadfast: ' . $e->getMessage());
        }

        $body = $response->json();

        if (!$response->successful()) {
            Log::warning('Steadfast rejected a request', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $body,
            ]);

            throw new SteadfastException(
                $this->errorMessage($body, $response->status()),
                ['status' => $response->status(), 'body' => $body],
                $response->status()
            );
        }

        return is_array($body) ? $body : [];
    }

    /**
     * Steadfast reports validation problems in a few shapes. Pull out
     * something an admin can act on instead of showing a bare status code.
     */
    protected function errorMessage($body, $status)
    {
        if (is_array($body)) {
            if (!empty($body['message']) && is_string($body['message'])) {
                return $body['message'];
            }

            if (!empty($body['errors']) && is_array($body['errors'])) {
                $first = reset($body['errors']);

                return is_array($first) ? (string) reset($first) : (string) $first;
            }
        }

        return 'Steadfast returned HTTP ' . $status . '.';
    }
}
