<?php

namespace App\Services;

use App\Models\CourierShipment;
use App\Models\IntegrationSetting;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourierGateway
{
    public const DEFAULTS = ['steadfast' => 'https://portal.steadfast.com.bd/api/v1', 'pathao' => 'https://api-hermes.pathao.com', 'redx' => 'https://openapi.redx.com.bd/v1.0.0-beta'];

    public static function validBase(string $provider, string $url): bool
    {
        $allowed = ['steadfast' => ['portal.steadfast.com.bd', 'portal.packzy.com'], 'pathao' => ['api-hermes.pathao.com', 'courier-api-sandbox.pathao.com'], 'redx' => ['openapi.redx.com.bd', 'sandbox.redx.com.bd']];

        return parse_url($url, PHP_URL_SCHEME) === 'https' && in_array(parse_url($url, PHP_URL_HOST), $allowed[$provider] ?? [], true) && ! parse_url($url, PHP_URL_USER) && ! parse_url($url, PHP_URL_PASS) && ! parse_url($url, PHP_URL_QUERY) && ! parse_url($url, PHP_URL_FRAGMENT) && in_array(parse_url($url, PHP_URL_PORT), [null, 443], true);
    }

    private function settings(string $provider): array
    {
        $row = IntegrationSetting::where('provider', $provider)->first();
        $s = array_merge($row?->settings ?? [], $row?->credentials ?? []);
        $s['base_url'] = rtrim($s['base_url'] ?? self::DEFAULTS[$provider] ?? '', '/');
        $required = ['steadfast' => ['api_key', 'secret_key'], 'pathao' => ['store_id', 'client_id', 'client_secret', 'client_email', 'client_password'], 'redx' => ['store_id', 'api_token']];
        if (! isset($required[$provider]) || ! self::validBase($provider, $s['base_url'])) {
            throw ValidationException::withMessages(['courier' => 'Set a valid official HTTPS courier API URL in Courier setup.']);
        }
        foreach ($required[$provider] as $key) {
            if (empty($s[$key])) {
                throw ValidationException::withMessages(['courier' => 'Complete the '.$provider.' credentials and store settings in Courier setup first.']);
            }
        }
        if ($provider !== 'steadfast' && (! ctype_digit((string) $s['store_id']) || (int) $s['store_id'] < 1)) {
            throw ValidationException::withMessages(['courier' => 'Enter a valid positive pickup store ID in Courier setup.']);
        }

        return $s;
    }

    private function tokenKey(array $s): string
    {
        return 'pathao-token-'.hash('sha256', json_encode([$s['base_url'], $s['client_id'], $s['client_secret'], $s['client_email'], $s['client_password']]));
    }

    private function client(string $provider, array $s)
    {
        $client = Http::acceptJson()->asJson()->connectTimeout(10)->timeout(30)->withOptions(['allow_redirects' => false]);
        if ($provider === 'steadfast') {
            return $client->withHeaders(['Api-Key' => $s['api_key'], 'Secret-Key' => $s['secret_key']]);
        }
        if ($provider === 'redx') {
            return $client->withHeaders(['API-ACCESS-TOKEN' => 'Bearer '.preg_replace('/^Bearer\s+/i', '', $s['api_token'])]);
        }
        $key = $this->tokenKey($s);
        $token = Cache::get($key);
        if (! $token) {
            try {
                $response = $client->post($s['base_url'].'/aladdin/api/v1/issue-token', ['client_id' => $s['client_id'], 'client_secret' => $s['client_secret'], 'username' => $s['client_email'], 'password' => $s['client_password'], 'grant_type' => 'password']);
            } catch (\Throwable $e) {
                throw new CourierApiException(true);
            }
            $data = $this->read($response);
            $token = $data['access_token'] ?? null;
            if (! is_string($token) || $token === '') {
                throw new CourierApiException(true);
            }
            Cache::put($key, $token, max(30, min((int) ($data['expires_in'] ?? 3600) - 60, 3600)));
        }

return $client->withToken($token);
    }

    private function read($response): array
    {
        if (! $response->successful()) {
            throw new CourierApiException(in_array($response->status(), [400, 401, 403, 422], true));
        }
        $data = $response->json();
        if (! is_array($data)) {
            throw new CourierApiException;
        }
        if (isset($data['status']) && is_numeric($data['status']) && (int) $data['status'] >= 400) {
            throw new CourierApiException(in_array((int) $data['status'], [400, 401, 403, 422], true));
        }
        if (($data['type'] ?? '') === 'error') {
            throw new CourierApiException(true);
        }

        return $data;
    }

    private function get(string $provider, string $path): array
    {
        $s = $this->settings($provider);
        try {
            $response = $this->client($provider, $s)->get($s['base_url'].$path);
            if ($provider === 'pathao' && $response->status() === 401) {
                Cache::forget($this->tokenKey($s));
            }

return $this->read($response);
        } catch (ValidationException|CourierApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new CourierApiException;
        }
    }

    public function check(string $provider): void
    {
        $this->get($provider, match ($provider) {
            'steadfast' => '/get_balance','pathao' => '/aladdin/api/v1/stores','redx' => '/pickup/stores',default => throw new CourierApiException(true)
        });
    }

    public function areas(): array
    {
        $data = $this->get('redx', '/areas');
        $areas = $data['areas'] ?? $data['data'] ?? [];

        return collect($areas)->filter(fn ($a) => isset($a['id'],$a['name']))->map(fn ($a) => ['id' => (int) $a['id'], 'name' => $a['name'], 'district' => $a['district_name'] ?? ''])->values()->all();
    }

    public function assertConfigured(string $provider): void
    {
        $this->settings($provider);
    }

    public function book(string $provider, Order $order, array $options): array
    {
        $s = $this->settings($provider);
        $phone = preg_replace('/[^0-9]/', '', $order->phone);
        if (str_starts_with($phone, '880')) {
            $phone = '0'.substr($phone, 3);
        }
        if (! preg_match('/^01[3-9][0-9]{8}$/', $phone)) {
            throw ValidationException::withMessages(['courier' => 'Use a valid Bangladeshi recipient mobile number on this order.']);
        }
        $amount = $order->payment_status === 'paid' ? 0 : (float) $order->total;
        $invoice = (string) (10000 + $order->id);
        $weight = (float) $options['weight'];
        $payload = match ($provider) {
            'steadfast' => ['invoice' => $invoice, 'recipient_name' => $order->customer_name, 'recipient_phone' => $phone, 'recipient_address' => $order->address, 'cod_amount' => $amount, 'note' => $order->customer_note ?? ''],
            'pathao' => ['store_id' => (int) $s['store_id'], 'merchant_order_id' => $invoice, 'recipient_name' => $order->customer_name, 'recipient_phone' => $phone, 'recipient_address' => $order->address, 'delivery_type' => 48, 'item_type' => 2, 'item_quantity' => $order->items()->sum('quantity'), 'item_weight' => $weight, 'amount_to_collect' => $amount, 'item_description' => Str::limit($order->items->pluck('product_name')->implode(', '), 250, ''), 'special_instruction' => $order->customer_note ?? ''],
            'redx' => ['pickup_store_id' => (int) $s['store_id'], 'merchant_invoice_id' => $invoice, 'customer_name' => $order->customer_name, 'customer_phone' => $phone, 'customer_address' => $order->address, 'delivery_area_id' => (int) $options['area_id'], 'delivery_area' => $options['area_name'], 'parcel_weight' => (int) round($weight * 1000), 'cash_collection_amount' => $amount, 'value' => (float) $order->subtotal, 'instruction' => $order->customer_note ?? ''],
        };
        try {
            $response = $this->client($provider, $s)->post($s['base_url'].match ($provider) {
                'steadfast' => '/create_order','pathao' => '/aladdin/api/v1/orders','redx' => '/parcel'
            }, $payload);
            if ($provider === 'pathao' && $response->status() === 401) {
                Cache::forget($this->tokenKey($s));
            }$body = $this->read($response);
            $id = match ($provider) {
                'steadfast' => $body['consignment']['consignment_id'] ?? null,'pathao' => $body['data']['consignment_id'] ?? null,'redx' => $body['tracking_id'] ?? null
            };
            if (! $id || strlen((string) $id) > 100) {
                throw new CourierApiException;
            }

            return ['consignment_id' => (string) $id, 'tracking_code' => (string) ($provider === 'steadfast' ? ($body['consignment']['tracking_code'] ?? $id) : $id), 'remote_status' => (string) ($provider === 'steadfast' ? ($body['consignment']['status'] ?? 'pending') : ($body['data']['order_status'] ?? 'pending'))];
        } catch (ValidationException|CourierApiException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new CourierApiException;
        }
    }

    public function status(CourierShipment $shipment): string
    {
        $id = rawurlencode($shipment->consignment_id);
        $data = $this->get($shipment->provider, match ($shipment->provider) {
            'steadfast' => '/status_by_cid/'.$id,'pathao' => '/aladdin/api/v1/orders/'.$id.'/info','redx' => '/parcel/info/'.$id
        });
        $status = match ($shipment->provider) {
            'steadfast' => $data['delivery_status'] ?? null,'pathao' => $data['data']['order_status'] ?? null,'redx' => $data['parcel']['status'] ?? $data['status'] ?? null
        };
        if (! is_string($status) || $status === '' || strlen($status) > 100) {
            throw new CourierApiException;
        }

        return $status;
    }
}
