<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class OrderController extends Controller
{
    private const STATUSES = ['pending', 'processing', 'on_the_way', 'on_hold', 'courier', 'completed', 'cancelled'];

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->hasPermission('orders.manage'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(self::STATUSES)],
            'search' => 'nullable|string|max:200',
        ]);
        $records = Order::with(['items', 'shipment'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('customer_name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                    $number = ltrim($search, '#');
                    if (ctype_digit($number)) {
                        $id = (int) $number;
                        $query->orWhere('id', $id >= 10000 ? $id - 10000 : $id);
                    }
                });
            })
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Orders', [
            'records' => $records,
            'filters' => $filters,
            'summary' => [
                'total' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'completed' => Order::where('status', 'completed')->count(),
                'cancelled' => Order::where('status', 'cancelled')->count(),
                'revenue' => Order::where('status', 'completed')->sum('total'),
            ],
            'statuses' => self::STATUSES,
        ]);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorizeAccess($request);
        $order->load(['items', 'customer', 'shipment']);

        return Inertia::render('OrderDetails', [
            'order' => $order,
            'statuses' => self::STATUSES,
            'couriers' => ['pathao' => 'Pathao', 'redx' => 'REDX', 'steadfast' => 'Steadfast'],
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $this->authorizeAccess($request);
        $data = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'payment_status' => ['required', Rule::in(['paid', 'unpaid'])],
            'courier_provider' => ['nullable', Rule::in(['pathao', 'redx', 'steadfast'])],
        ]);

        DB::transaction(function () use ($order, $data) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $lines = $order->items()->get();
            if ($data['status'] === 'cancelled' && $order->stock_reserved) {
                $this->restoreStock($lines);
                $order->stock_reserved = false;
            } elseif ($data['status'] !== 'cancelled' && ! $order->stock_reserved) {
                $this->reserveStock($lines);
                $order->stock_reserved = true;
            }

            $shipment = $order->shipment;
            if ($shipment && $shipment->state === 'booked' && ($data['courier_provider'] ?? null) !== $shipment->provider) {
                throw ValidationException::withMessages(['courier_provider' => 'A booked courier cannot be changed.']);
            }
            $order->fill($data)->save();
        });

        return back()->with('success', 'Order updated successfully.');
    }

    public function destroy(Request $request, Order $order)
    {
        $this->authorizeAccess($request);
        DB::transaction(function () use ($order) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($order->shipment()->exists()) {
                throw ValidationException::withMessages(['order' => 'Keep orders with courier booking records for tracking and reconciliation.']);
            }
            if ($order->stock_reserved) {
                $this->restoreStock($order->items()->get());
            }
            $order->delete();
        });

        return redirect('/admin/orders')->with('success', 'Order deleted successfully.');
    }

    private function restoreStock($lines): void
    {
        foreach ($lines as $line) {
            $stock = $line->variant_id
                ? ProductVariant::whereKey($line->variant_id)->lockForUpdate()->first()
                : Product::whereKey($line->product_id)->lockForUpdate()->first();
            $stock?->increment('stock', $line->quantity);
        }
    }

    private function reserveStock($lines): void
    {
        foreach ($lines as $line) {
            $stock = $line->variant_id
                ? ProductVariant::whereKey($line->variant_id)->lockForUpdate()->first()
                : Product::whereKey($line->product_id)->lockForUpdate()->first();
            if (! $stock || $stock->stock < $line->quantity) {
                throw ValidationException::withMessages([
                    'status' => 'This order cannot be reopened because an item is no longer available in the required quantity.',
                ]);
            }
            $stock->decrement('stock', $line->quantity);
        }
    }
}
