<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShippingController extends Controller
{
    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->hasPermission('shipping.manage'), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAccess($request);
        $settings = SiteSetting::current();

        return Inertia::render('Shipping', [
            'charges' => [
                'inside_dhaka' => (float) ($settings['shipping_inside_dhaka'] ?? 80),
                'outside_dhaka' => (float) ($settings['shipping_outside_dhaka'] ?? 150),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizeAccess($request);
        $data = $request->validate([
            'inside_dhaka' => 'required|numeric|min:0|max:999999.99',
            'outside_dhaka' => 'required|numeric|min:0|max:999999.99',
        ]);
        $settings = SiteSetting::current();
        $settings['shipping_inside_dhaka'] = round((float) $data['inside_dhaka'], 2);
        $settings['shipping_outside_dhaka'] = round((float) $data['outside_dhaka'], 2);
        SiteSetting::updateOrCreate(['id' => 1], ['data' => $settings]);

        return back()->with('success', 'Shipping charges updated successfully.');
    }
}
