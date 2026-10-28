<?php
namespace App\Services;

use App\Models\{IntegrationSetting, Order, PaymentAttempt, Product, ProductVariant};
use Illuminate\Support\Facades\{DB, Http};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Sslcommerz
{
    public function available(): bool {
        $row = IntegrationSetting::where('provider', 'sslcommerz')->first();
        return (bool) ($row?->settings['status'] ?? false) && !empty($row?->settings['store_id']) && !empty($row?->credentials['store_password']);
    }
    private function base(string $mode): string { return $mode === 'live' ? 'https://securepay.sslcommerz.com' : 'https://sandbox.sslcommerz.com'; }
    public function initiate(Order $order): string {
        if (!$this->available()) throw ValidationException::withMessages(['payment_method' => 'Online payment is currently unavailable. Choose cash on delivery.']);
        $row = IntegrationSetting::where('provider', 'sslcommerz')->firstOrFail();
        $attempt = PaymentAttempt::create([
            'order_id' => $order->id, 'transaction_id' => 'RN'.Str::uuid()->toString(),
            'mode' => $row->settings['mode'] ?? 'test', 'amount' => $order->total,
            'credentials' => ['store_id' => $row->settings['store_id'], 'store_passwd' => $row->credentials['store_password']],
        ]);
        try {
            $response = Http::asForm()->connectTimeout(10)->timeout(30)->post($this->base($attempt->mode).'/gwprocess/v4/api.php', [
                ...$attempt->credentials, 'total_amount' => $attempt->amount, 'currency' => 'BDT', 'tran_id' => $attempt->transaction_id,
                'success_url' => route('payments.sslcommerz.return', ['attempt' => $attempt->transaction_id, 'result' => 'success']),
                'fail_url' => route('payments.sslcommerz.return', ['attempt' => $attempt->transaction_id, 'result' => 'fail']),
                'cancel_url' => route('payments.sslcommerz.return', ['attempt' => $attempt->transaction_id, 'result' => 'cancel']),
                'ipn_url' => route('payments.sslcommerz.ipn'),
                'cus_name' => $order->customer_name, 'cus_phone' => $order->phone,
                'cus_email' => $order->email ?: 'guest@example.com', 'cus_add1' => $order->address,
                'cus_city' => $order->area === 'inside_dhaka' ? 'Dhaka' : 'Bangladesh', 'cus_postcode' => '1000', 'cus_country' => 'Bangladesh',
                'shipping_method' => 'YES', 'ship_name' => $order->customer_name, 'ship_add1' => $order->address,
                'ship_city' => $order->area === 'inside_dhaka' ? 'Dhaka' : 'Bangladesh', 'ship_postcode' => '1000', 'ship_country' => 'Bangladesh',
                'num_of_item' => $order->items()->sum('quantity'), 'product_name' => 'Order #'.(10000+$order->id),
                'product_category' => 'Retail', 'product_profile' => 'physical-goods',
            ]);
            $body = $response->json();
            $url = $body['GatewayPageURL'] ?? '';
            $host = parse_url($url, PHP_URL_HOST);
            if (!$response->successful() || ($body['status'] ?? '') !== 'SUCCESS' || parse_url($url, PHP_URL_SCHEME) !== 'https' || !is_string($host) || !($host === 'sslcommerz.com' || str_ends_with($host, '.sslcommerz.com'))) {
                throw new \RuntimeException('Gateway initiation rejected.');
            }
            return $url;
        } catch (\Throwable $exception) {
            $this->release($attempt, 'failed');
            // Never log HTTP exceptions containing merchant credentials.
            throw ValidationException::withMessages(['payment_method' => 'Unable to open SSLcommerz. Please try again or choose cash on delivery.']);
        }
    }
    public function verify(PaymentAttempt $attempt, string $validationId): bool {
        $response = Http::connectTimeout(10)->timeout(30)->get($this->base($attempt->mode).'/validator/api/validationserverAPI.php', [
            ...$attempt->credentials, 'val_id' => $validationId, 'format' => 'json',
        ]);
        $data = $response->json();
        if (!$response->successful() || !in_array($data['status'] ?? '', ['VALID', 'VALIDATED'], true)
            || ($data['tran_id'] ?? '') !== $attempt->transaction_id || ($data['val_id'] ?? '') !== $validationId
            || ($data['currency'] ?? '') !== 'BDT' || !is_numeric($data['amount'] ?? null)
            || (int) round((float)$data['amount'] * 100) !== (int) round((float)$attempt->amount * 100)) return false;
        return DB::transaction(function () use ($attempt, $validationId, $data) {
            $record = PaymentAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($record->order_id)->lockForUpdate()->firstOrFail();
            if (in_array($record->status, ['paid', 'review'], true)) return true;
            if (PaymentAttempt::where('validation_id', $validationId)->where('id', '!=', $record->id)->exists()) return false;
            $review = (string)($data['risk_level'] ?? '1') !== '0' || !$order->stock_reserved || $order->status === 'cancelled';
            $record->update(['status' => $review ? 'review' : 'paid', 'validation_id' => $validationId]);
            $order->payment_status = 'paid';
            if ($review) $order->status = 'on_hold';
            $order->save();
            return true;
        });
    }
    public function verifyFailure(PaymentAttempt $attempt): void {
        $response = Http::connectTimeout(10)->timeout(30)->get($this->base($attempt->mode).'/validator/api/merchantTransIDvalidationAPI.php', [
            ...$attempt->credentials, 'tran_id' => $attempt->transaction_id, 'format' => 'json',
        ]);
        if (!$response->successful()) return;
        foreach ($response->json('element', []) as $record) {
            if (($record['tran_id'] ?? '') === $attempt->transaction_id && in_array($record['status'] ?? '', ['FAILED', 'CANCELLED'], true)) {
                $this->release($attempt, strtolower($record['status']));
                return;
            }
        }
    }
    private function release(PaymentAttempt $attempt, string $status): void {
        DB::transaction(function () use ($attempt, $status) {
            $record = PaymentAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($record->order_id)->lockForUpdate()->firstOrFail();
            if ($order->payment_status === 'paid') return;
            if ($order->stock_reserved) {
                foreach ($order->items as $item) {
                    $stock = $item->variant_id ? ProductVariant::whereKey($item->variant_id)->lockForUpdate()->first() : Product::whereKey($item->product_id)->lockForUpdate()->first();
                    $stock?->increment('stock', $item->quantity);
                }
            }
            $order->update(['status' => 'cancelled', 'stock_reserved' => false]);
            $record->update(['status' => $status]);
        });
    }
}
