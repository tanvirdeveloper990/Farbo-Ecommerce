<?php
namespace App\Http\Controllers;
use App\Models\PaymentAttempt;
use App\Services\Sslcommerz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SslcommerzController extends Controller {
    public function callback(Request $request, string $attempt, string $result, Sslcommerz $gateway) {
        $record = PaymentAttempt::where('transaction_id', $attempt)->firstOrFail();
        $verified = false;
        try {
            if (is_string($request->input('val_id')) && strlen($request->input('val_id')) <= 255) $verified = $gateway->verify($record, $request->input('val_id'));
            elseif (in_array($result, ['fail', 'cancel'], true)) $gateway->verifyFailure($record);
        } catch (\Throwable $exception) {
            // Verification can be retried by IPN; never expose merchant credentials.
        }
        $order = $record->order->fresh();
        // Gateway POSTs are sessionless so cross-site callbacks cannot overwrite login cookies.
        return redirect()->to(URL::temporarySignedRoute('orders.success', now()->addDays(30), ['order' => $order->id, 'payment_result' => 'returned']), 303);
    }
    public function ipn(Request $request, Sslcommerz $gateway) {
        $data = $request->validate(['tran_id' => 'required|string|max:50', 'val_id' => 'required|string|max:255']);
        $record = PaymentAttempt::where('transaction_id', $data['tran_id'])->firstOrFail();
        try { $verified = $gateway->verify($record, $data['val_id']); }
        catch (\Throwable $exception) { return response()->json(['verified' => false], 503); }
        return response()->json(['verified' => $verified], $verified ? 200 : 422);
    }
}
