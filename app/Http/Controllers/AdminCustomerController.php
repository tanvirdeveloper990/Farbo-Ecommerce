<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminCustomerController extends Controller
{
    private function checkoutCustomers()
    {
        return Order::where(function ($query) {
            $query->whereNull('customer_id')->orWhereHas('customer', function ($user) {
                $user->where('is_admin', true)->orWhere('is_super_admin', true);
            });
        });
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('users.manage'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        $search = $request->validate(['search' => 'nullable|string|max:150'])['search'] ?? '';
        $buyers = DB::table('orders')->selectRaw("id, 'order' as kind, customer_name as name, email, phone, address, created_at, 1 as orders_count, created_at as last_order_at");
        $accounts = DB::table('users')->where('is_admin', false)->where('is_super_admin', false)
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('orders')->whereColumn('orders.customer_id', 'users.id'))
            ->selectRaw("id, 'account' as kind, name, email, phone, address, created_at, 0 as orders_count, NULL as last_order_at");
        $query = DB::query()->fromSub($buyers->unionAll($accounts), 'customers');
        if ($search !== '') {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')->orWhere('phone', 'like', '%'.$search.'%'));
        }

        return Inertia::render('Customers', [
            'records' => $query->orderByDesc('last_order_at')->orderByDesc('created_at')->orderBy('kind')->orderByDesc('id')->paginate(50)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function show(Request $request, string $kind, int $id)
    {
        $this->authorizeAccess($request);
        if ($kind === 'order') {
            $account = null;
            $query = Order::whereKey($id);
            $query->firstOrFail();
        } elseif ($kind === 'account') {
            $account = User::where('is_admin', false)->where('is_super_admin', false)->findOrFail($id);
            $query = Order::where('customer_id', $account->id);
        } elseif ($kind === 'guest') {
            $guest = $this->checkoutCustomers()->findOrFail($id);
            $account = null;
            $query = $this->checkoutCustomers()->whereKey($guest->id);
        } else {
            abort(404);
        }
        $latest = (clone $query)->latest('id')->first();
        if ($latest) $query->whereKey($latest->id);

        return Inertia::render('CustomerDetails', [
            'customer' => [
                'name' => $latest?->customer_name ?? $account?->name,
                'phone' => $latest?->phone ?? $account?->phone,
                'email' => $latest?->email ?? $account?->email,
                'address' => $latest?->address ?? $account?->address,
                'kind' => $kind,
                'order_id' => $latest?->id,
                'account' => $account?->only(['name', 'email', 'phone', 'address', 'created_at']),
                'orders_count' => (clone $query)->count(),
                'total_order_value' => (clone $query)->sum('total'),
            ],
            'orders' => $query->with('items')->latest('id')->paginate(50)->withQueryString(),
        ]);
    }
}
