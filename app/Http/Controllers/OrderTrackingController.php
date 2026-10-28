<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderTrackingController extends Controller
{
    public function index(Request $request)
    {
        Inertia::setRootView('storefront');
        $input = $request->validate([
            'order_id' => ['nullable', 'string', 'max:20', 'regex:/^#?[0-9]{1,18}$/'],
        ]);
        $reference = $input['order_id'] ?? '';
        $order = null;
        if ($reference !== '') {
            // Customers receive order numbers starting at 10001 on their receipt.
            $id = (int) ltrim($reference, '#') - 10000;
            $record = $id > 0 ? Order::with(['items', 'shipment'])->find($id) : null;
            if ($record) {
                // Public tracking contains purchase details, never personal contact information.
                $order = [
                    'number' => 10000 + $record->id,
                    'created_at' => $record->created_at->toIso8601String(),
                    'status' => $record->status,
                    'payment_method' => $record->payment_method,
                    'payment_status' => $record->payment_status,
                    'courier_provider' => $record->courier_provider,
                    'courier_tracking' => $record->shipment?->tracking_code,
                    'courier_status' => $record->shipment?->remote_status,
                    'discount_amount' => (float) ($record->discount_amount ?? 0),
                    'subtotal' => (float) $record->subtotal,
                    'delivery_fee' => (float) $record->delivery_fee,
                    'total' => (float) $record->total,
                    'items' => $record->items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->product_name,
                        'variant' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                        'total' => (float) $item->total,
                    ]),
                ];
            }
        }

        return Inertia::render('Storefront/TrackOrder', [
            'order' => $order,
            'orderId' => $reference,
            'searched' => $reference !== '',
        ]);
    }
}
