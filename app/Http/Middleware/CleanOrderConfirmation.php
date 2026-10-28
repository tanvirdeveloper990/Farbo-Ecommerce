<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class CleanOrderConfirmation {
    public function handle(Request $request, Closure $next) {
        $order = $request->route('order');
        $id = (string) ($order instanceof \App\Models\Order ? $order->id : $order);
        if ($request->hasValidSignature()) {
            $access = $request->session()->get('order_confirmation_access', []);
            $access[$id] = now()->addDays(30)->timestamp;
            $request->session()->put('order_confirmation_access', array_slice($access, -20, null, true));
            $record = $order instanceof \App\Models\Order ? $order : \App\Models\Order::findOrFail($id);
            if ($request->query('payment_result') === 'returned') {
                if ($record->payment_status === 'paid') {
                    $request->session()->put('checkout_order_id', $record->id);
                    $request->session()->flash('success', 'Your payment has been verified.');
                } else {
                    $request->session()->forget('checkout_order_id');
                    $request->session()->forget('success');
                    $request->session()->flash('error', 'Payment is not confirmed. Your cart is preserved.');
                }
            }
            // Redirect before analytics loads so access tokens cannot leak in tracked URLs.
            return redirect('/order-success/'.$id, 303);
        }
        abort_unless(($request->session()->get('order_confirmation_access', [])[$id] ?? 0) > now()->timestamp, 403);
        return $next($request);
    }
}
