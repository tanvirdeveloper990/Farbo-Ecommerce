<?php

namespace App\Http\Controllers;

use App\Models\CourierShipment;
use App\Models\Order;
use App\Services\CourierApiException;
use App\Services\CourierGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourierController extends Controller
{
    private function authorize(Request $r, string $permission = 'orders.manage'): void
    {
        abort_unless($r->user()?->hasPermission($permission), 403);
    }

    public function connection(Request $r, string $provider, CourierGateway $gateway)
    {
        $this->authorize($r, 'couriers.manage');
        try {
            $gateway->check($provider);
        } catch (CourierApiException $e) {
            throw ValidationException::withMessages(['courier' => $e->getMessage()]);
        }

return back()->with('success', ucfirst($provider).' connection verified.');
    }

    public function areas(Request $r, CourierGateway $gateway)
    {
        $this->authorize($r);
        try {
            return response()->json(['areas' => $gateway->areas()]);
        } catch (CourierApiException $e) {
            return response()->json(['message' => 'Unable to load REDX delivery areas. Check courier credentials.'], 422);
        }
    }

    public function book(Request $r, Order $order, CourierGateway $gateway)
    {
        $this->authorize($r);
        $data = $r->validate(['provider' => 'required|in:steadfast,pathao,redx', 'weight' => 'required|numeric|min:0.1|max:10', 'area_id' => 'required_if:provider,redx|nullable|integer|min:1', 'area_name' => 'required_if:provider,redx|nullable|string|max:160']);
        $gateway->assertConfigured($data['provider']);
        [$shipment,$snapshot] = DB::transaction(function () use ($order, $data) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, ['cancelled', 'completed', 'on_hold'], true) || ! $locked->stock_reserved) {
                throw ValidationException::withMessages(['courier' => 'Only active orders with reserved stock can be booked.']);
            }
            if ($locked->payment_method !== 'cash_on_delivery' && $locked->payment_status !== 'paid') {
                throw ValidationException::withMessages(['courier' => 'Verify the online payment before courier booking.']);
            }
            $existing = CourierShipment::where('order_id', $locked->id)->first();
            if ($existing && $existing->state !== 'rejected') {
                throw ValidationException::withMessages(['courier' => 'This order already has a booking or an uncertain request. Check its tracking details or reconcile the existing parcel.']);
            }
            $values = ['provider' => $data['provider'], 'state' => 'creating', 'cod_amount' => $locked->payment_status === 'paid' ? 0 : $locked->total, 'options' => $data];
            if ($existing) {
                $existing->update($values);

                return [$existing, $locked->load('items')];
            }

return [CourierShipment::create(['order_id' => $locked->id, ...$values]), $locked->load('items')];
        });
        try {
            $result = $gateway->book($data['provider'], $snapshot, $data);
            DB::transaction(function () use ($order, $shipment, $result, $data) {
                $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
                $shipment->update([...$result, 'state' => 'booked', 'synced_at' => now()]);
                $locked->update(['courier_provider' => $data['provider'], 'status' => in_array($locked->status, ['cancelled', 'completed', 'on_hold'], true) ? $locked->status : 'courier']);
            });
        } catch (ValidationException $e) {
            $shipment->update(['state' => 'rejected']);
            throw $e;
        } catch (CourierApiException $e) {
            $shipment->update(['state' => $e->definitelyRejected ? 'rejected' : 'unknown']);
            throw ValidationException::withMessages(['courier' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $shipment->update(['state' => 'unknown']);
            throw ValidationException::withMessages(['courier' => 'Booking result is uncertain. Check the courier portal before taking further action.']);
        }

        return back()->with('success', 'Courier booking created. Consignment: '.$shipment->fresh()->consignment_id);
    }

    public function sync(Request $r, Order $order, CourierGateway $gateway)
    {
        $this->authorize($r);
        $shipment = CourierShipment::where('order_id', $order->id)->where('state', 'booked')->firstOrFail();
        try {
            $status = $gateway->status($shipment);
        } catch (CourierApiException $e) {
            throw ValidationException::withMessages(['courier' => 'Unable to refresh courier status. Please check credentials or try again later.']);
        }
        DB::transaction(function () use ($order, $shipment, $status) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $shipment->update(['remote_status' => $status, 'synced_at' => now()]);
            $normalized = strtolower(str_replace([' ', '_'], '-', trim($status)));
            $mapped = match ($normalized) {
                'delivered','delivered-approval-pending' => 'completed','in-transit','on-the-way','out-for-delivery','delivery-in-progress' => 'on_the_way','cancelled','canceled','returned','returned-to-merchant','partial-delivered','partially-delivered','hold' => 'on_hold',default => null
            };
            if ($mapped && ! in_array($locked->status, ['cancelled', 'completed', 'on_hold'], true)) {
                $locked->update(['status' => $mapped]);
            }
        });

        return back()->with('success', 'Courier status refreshed.');
    }

    public function attach(Request $r, Order $order, CourierGateway $gateway)
    {
        $this->authorize($r);
        $data = $r->validate(['consignment_id' => 'required|string|max:100|regex:/^[A-Za-z0-9_-]+$/', 'confirmed' => 'accepted']);
        $shipment = CourierShipment::where('order_id', $order->id)->firstOrFail();
        if (! ($shipment->state === 'unknown' || ($shipment->state === 'creating' && $shipment->updated_at->lt(now()->subMinutes(2))))) {
            throw ValidationException::withMessages(['courier' => 'Only uncertain or stalled bookings can be reconciled.']);
        }
        $shipment->consignment_id = $data['consignment_id'];
        try {
            $status = $gateway->status($shipment);
        } catch (CourierApiException $e) {
            throw ValidationException::withMessages(['courier' => 'That consignment could not be verified with the courier.']);
        }
        DB::transaction(function () use ($order, $shipment, $data, $status) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $shipment->update(['state' => 'booked', 'consignment_id' => $data['consignment_id'], 'tracking_code' => $data['consignment_id'], 'remote_status' => $status, 'synced_at' => now()]);
            $locked->update(['courier_provider' => $shipment->provider, 'status' => in_array($locked->status, ['cancelled', 'completed', 'on_hold'], true) ? $locked->status : 'courier']);
        });

        return back()->with('success','Existing courier parcel linked; no duplicate booking was sent.');
    }
}
