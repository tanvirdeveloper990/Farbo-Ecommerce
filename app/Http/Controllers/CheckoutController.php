<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Policy;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        Inertia::setRootView('storefront');
        $settings = SiteSetting::current();

        return Inertia::render('Storefront/Checkout', [
            'customer' => $request->user()
                ? $request->user()->only(['name', 'email', 'phone', 'address'])
                : null,
            'shippingCharges' => [
                'inside_dhaka' => (float) ($settings['shipping_inside_dhaka'] ?? 80),
                'outside_dhaka' => (float) ($settings['shipping_outside_dhaka'] ?? 150),
            ],
            'freeShippingThreshold' => 5000,
            'sslcommerzAvailable' => app(\App\Services\Sslcommerz::class)->available(),
            'sslcommerzSandbox' => (\App\Models\IntegrationSetting::where('provider', 'sslcommerz')->first()?->settings['mode'] ?? 'test') === 'test',
            'policies' => Policy::whereIn('slug', ['refund-policy', 'privacy-policy'])
                ->get(['title', 'slug']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:160',
            'phone' => 'required|string|min:7|max:40',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:2000',
            'area' => 'required|in:inside_dhaka,outside_dhaka',
            'payment_method' => 'required|in:cash_on_delivery,sslcommerz,bkash,nagad',
            'policy_accepted' => 'accepted',
            'coupon_code' => 'nullable|string|max:60',
            'gift_card_code' => 'nullable|string|max:60',
            'customer_note' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1|max:30',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);

        if ($data['payment_method'] !== 'cash_on_delivery' && !app(\App\Services\Sslcommerz::class)->available()) {
            throw ValidationException::withMessages(['payment_method' => 'Online payment is unavailable. Choose cash on delivery.']);
        }
        $order = DB::transaction(function () use ($request, $data) {
            $productIds = collect($data['items'])->pluck('product_id')->unique();
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');
            $variantIds = collect($data['items'])->pluck('variant_id')->filter()->unique();
            $variants = ProductVariant::whereIn('id', $variantIds)->lockForUpdate()->get()->keyBy('id');
            $subtotalCents = 0;
            $lines = [];
            $seenLines = [];

            foreach ($data['items'] as $index => $item) {
                $lineKey = $item['product_id'].'-'.($item['variant_id'] ?? 'default');
                if (isset($seenLines[$lineKey])) {
                    throw ValidationException::withMessages(["items.$index.product_id" => 'Each product option can only appear once in your cart.']);
                }
                $seenLines[$lineKey] = true;

                $product = $products->get($item['product_id']);
                if (! $product || ! $product->status) {
                    throw ValidationException::withMessages(["items.$index.product_id" => 'This product is no longer available.']);
                }

                $variant = ! empty($item['variant_id']) ? $variants->get($item['variant_id']) : null;
                if (($item['variant_id'] ?? null) && (! $variant || $variant->product_id !== $product->id)) {
                    throw ValidationException::withMessages(["items.$index.variant_id" => 'Choose a valid option for this product.']);
                }

                $hasVariants = $product->variants()->exists();
                if ($hasVariants && ! $variant) {
                    throw ValidationException::withMessages(["items.$index.variant_id" => 'Choose a product option before ordering.']);
                }

                $stockOwner = $variant ?? $product;
                if ($stockOwner->stock < $item['quantity']) {
                    throw ValidationException::withMessages(["items.$index.quantity" => $product->name.' does not have enough stock.']);
                }

                $price = $variant
                    ? (float) $variant->price
                    : (float) (($product->sale_price !== null && $product->sale_price > 0) ? $product->sale_price : $product->regular_price);
                if (! $product->hasPrice() || $price <= 0) {
                    throw ValidationException::withMessages(["items.$index.product_id" => $product->name.' cannot be ordered online. Please contact us on WhatsApp or Messenger.']);
                }
                $unitPriceCents = (int) round($price * 100);
                $lineTotalCents = $unitPriceCents * $item['quantity'];
                $subtotalCents += $lineTotalCents;

                $variantName = $variant
                    ? collect([$variant->color?->name, $variant->size?->name])->filter()->implode(' / ')
                    : null;
                $lines[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'stock_owner' => $stockOwner,
                    'name' => $product->name,
                    'variant_name' => $variantName ?: null,
                    'unit_price' => $unitPriceCents / 100,
                    'quantity' => $item['quantity'],
                    'total' => $lineTotalCents / 100,
                ];
            }

            $subtotal = $subtotalCents / 100;
            $discounts = app(\App\Services\CheckoutDiscounts::class)->calculate($subtotal, $data['coupon_code'] ?? null, $data['gift_card_code'] ?? null, true);
            $shippingSettings = SiteSetting::current();
            $deliveryFee = $subtotal >= 5000
                ? 0
                : (float) ($shippingSettings[$data['area'] === 'inside_dhaka' ? 'shipping_inside_dhaka' : 'shipping_outside_dhaka']
                    ?? ($data['area'] === 'inside_dhaka' ? 80 : 150));
            $order = Order::create([
                'customer_id' => $request->user()?->id,
                'customer_name' => $data['customer_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'area' => $data['area'],
                'payment_method' => $data['payment_method'],
                'payment_status' => 'unpaid',
                'status' => 'pending',
                'customer_note' => $data['customer_note'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                ...$discounts,
                'total' => round($subtotal - $discounts['discount_amount'] + $deliveryFee, 2),
                'stock_reserved' => true,
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'variant_id' => $line['variant']?->id,
                    'product_name' => $line['name'],
                    'variant_name' => $line['variant_name'],
                    'unit_price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'total' => $line['total'],
                ]);
                $line['stock_owner']->decrement('stock', $line['quantity']);
            }

            return $order;
        });

        $request->session()->flash('success', 'Order #'.(10000 + $order->id).' placed successfully.');
        $request->session()->put('checkout_order_id', $order->id);

        if ($order->payment_method !== 'cash_on_delivery') {
            try {
                return Inertia::location(app(\App\Services\Sslcommerz::class)->initiate($order));
            } catch (ValidationException $exception) {
                $request->session()->forget('checkout_order_id');
                $request->session()->forget('success');
                throw $exception;
            }
        }

        return redirect()->to(URL::temporarySignedRoute(
            'orders.success',
            now()->addDays(30),
            ['order' => $order->id],
        ), 303);
    }

    public function success(Order $order, Request $request)
    {
        Inertia::setRootView('storefront');
        $order->load('items');
        if ($request->query('payment_result') === 'returned') {
            if ($order->payment_status === 'paid') {
                $request->session()->flash('success', 'Your payment has been verified.');
            } else {
                $request->session()->forget('checkout_order_id');
                $request->session()->forget('success');
                $request->session()->flash('error', 'Payment is not confirmed. Your cart is preserved.');
            }
        }

        return Inertia::render('Storefront/OrderSuccess', [
            'order' => $order,
            'clearCart' => $request->session()->pull('checkout_order_id') === $order->id,
            'invoiceDownloadUrl' => URL::temporarySignedRoute(
                'orders.invoice',
                now()->addDays(30),
                ['order' => $order->id],
            ),
        ]);
    }

    public function invoice(Order $order)
    {
        File::ensureDirectoryExists(storage_path('fonts'));
        $order->load('items');
        $settings = SiteSetting::current();
        $logo = null;
        $logoPath = parse_url($settings['header_logo'] ?? '', PHP_URL_PATH);
        if ($logoPath) {
            $file = realpath(public_path(ltrim($logoPath, '/')));
            $allowedRoots = [realpath(public_path()), realpath(storage_path('app/public'))];
            if ($file && collect($allowedRoots)->filter()->contains(fn ($root) => str_starts_with($file, $root.DIRECTORY_SEPARATOR)) && is_file($file)) {
                $mime = mime_content_type($file);
                if (in_array($mime, ['image/png', 'image/jpeg', 'image/gif'])) {
                    $logo = 'data:'.$mime.';base64,'.base64_encode(file_get_contents($file));
                }
            }
        }
        $pdf = Pdf::loadView('invoices.order', ['order' => $order, 'settings' => $settings, 'logo' => $logo])
            ->setPaper('a4');

        return $pdf->download('order-'.(10000 + $order->id).'-invoice.pdf');
    }
}
