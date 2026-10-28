<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerAccountController extends Controller
{
    private function authorizeCustomer(Request $request): void
    {
        abort_unless($request->user() && ! $request->user()->is_admin, 403);
    }

    public function profile(Request $request)
    {
        $this->authorizeCustomer($request);
        Inertia::setRootView('storefront');

        return Inertia::render('Profile', ['admin' => false]);
    }

    public function orders(Request $request)
    {
        $this->authorizeCustomer($request);
        Inertia::setRootView('storefront');
        $records = Order::with('items')
            ->where('customer_id', $request->user()->id)
            ->latest('id')
            ->paginate(10);

        return Inertia::render('CustomerOrders', [
            'records' => $records,
            'summary' => [
                'total' => Order::where('customer_id', $request->user()->id)->count(),
                'pending' => Order::where('customer_id', $request->user()->id)->whereIn('status', ['pending', 'processing', 'on_the_way', 'on_hold', 'courier'])->count(),
                'completed' => Order::where('customer_id', $request->user()->id)->where('status', 'completed')->count(),
            ],
        ]);
    }

    public function showOrder(Request $request, int $order)
    {
        $this->authorizeCustomer($request);
        Inertia::setRootView('storefront');
        $record = Order::with('items')
            ->where('customer_id', $request->user()->id)
            ->whereKey($order)
            ->firstOrFail();

        return Inertia::render('CustomerOrderDetails', [
            'order' => $record,
            'invoiceDownloadUrl' => \Illuminate\Support\Facades\URL::temporarySignedRoute('orders.invoice', now()->addMinutes(30), ['order' => $record->id]),
        ]);
    }
}
