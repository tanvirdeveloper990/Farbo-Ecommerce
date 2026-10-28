<?php

namespace App\Http\Controllers;

use App\Models\IntegrationSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class IntegrationSettingController extends Controller
{
    private const SCHEMAS = [
        'smtp' => ['title' => 'Update SMTP', 'permission' => 'smtp.manage', 'fields' => [
            ['key' => 'mailer', 'label' => 'Mailer', 'type' => 'select', 'required' => true, 'options' => ['smtp' => 'SMTP']],
            ['key' => 'host', 'label' => 'SMTP Host', 'type' => 'text', 'required' => true],
            ['key' => 'port', 'label' => 'SMTP Port', 'type' => 'number', 'required' => true],
            ['key' => 'username', 'label' => 'Username', 'type' => 'text', 'required' => true],
            ['key' => 'smtp_password', 'label' => 'Password', 'type' => 'password', 'required' => true],
            ['key' => 'encryption', 'label' => 'Encryption', 'type' => 'select', 'required' => false, 'options' => ['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL']],
            ['key' => 'from_address', 'label' => 'From Address', 'type' => 'email', 'required' => true],
            ['key' => 'from_name', 'label' => 'From Name', 'type' => 'text', 'required' => true],
        ]],
        'sslcommerz' => ['title' => 'SSLcommerz Setup', 'permission' => 'payments.manage', 'fields' => [
            ['key' => 'store_id', 'label' => 'SSLcz Store ID', 'type' => 'text', 'required' => true],
            ['key' => 'store_password', 'label' => 'SSLcz Password', 'type' => 'password', 'required' => true],
        ]],
        'steadfast' => ['title' => 'Update Steadfast', 'permission' => 'couriers.manage', 'fields' => [
            ['key' => 'base_url', 'label' => 'API Base URL', 'type' => 'url', 'required' => true],
            ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'required' => true],
            ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'required' => true],
        ]],
        'pathao' => ['title' => 'Update Pathao', 'permission' => 'couriers.manage', 'fields' => [
            ['key' => 'base_url', 'label' => 'API Base URL', 'type' => 'url', 'required' => true],
            ['key' => 'store_id', 'label' => 'Pickup Store ID', 'type' => 'text', 'required' => true],
            ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true],
            ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password', 'required' => true],
            ['key' => 'client_email', 'label' => 'Client Email', 'type' => 'email', 'required' => true],
            ['key' => 'client_password', 'label' => 'Client Password', 'type' => 'password', 'required' => true],
        ]],
        'redx' => ['title' => 'Update REDX', 'permission' => 'couriers.manage', 'fields' => [
            ['key' => 'base_url', 'label' => 'API Base URL', 'type' => 'url', 'required' => true],
            ['key' => 'store_id', 'label' => 'Pickup Store ID', 'type' => 'text', 'required' => true],
            ['key' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'required' => true],
        ]],
    ];

    private function authorizeAccess(Request $request, string $permission): void
    {
        abort_unless($request->user()->hasPermission($permission), 403);
    }

    private function display(string $provider): array
    {
        $row = IntegrationSetting::where('provider', $provider)->firstOrFail();
        $schema = self::SCHEMAS[$provider];
        $credentials = $row->credentials ?? [];
        $values = [];
        $saved = [];
        foreach ($schema['fields'] as $field) {
            $key = $field['key'];
            if ($field['type'] === 'password') {
                $saved[$key] = ! empty($credentials[$key]);
            } else {
                $values[$key] = $row->settings[$key] ?? ($key === 'base_url' ? (\App\Services\CourierGateway::DEFAULTS[$provider] ?? '') : '');
            }
        }
        if ($provider === 'sslcommerz') {
            $values['mode'] = $row->settings['mode'] ?? 'test';
            $values['status'] = (bool) ($row->settings['status'] ?? false);
        }

        return ['provider' => $provider, 'title' => $schema['title'], 'fields' => $schema['fields'], 'values' => $values, 'saved' => $saved, 'updated_at' => $row->updated_at?->toIso8601String()];
    }

    public function smtp(Request $request)
    {
        $this->authorizeAccess($request, 'smtp.manage');

        return Inertia::render('SmtpSetup', ['setup' => $this->display('smtp')]);
    }

    public function payments(Request $request)
    {
        $this->authorizeAccess($request, 'payments.manage');

        return Inertia::render('PaymentSetup', ['setup' => $this->display('sslcommerz')]);
    }

    public function couriers(Request $request)
    {
        $this->authorizeAccess($request, 'couriers.manage');

        return Inertia::render('CourierSetup', ['steadfast' => $this->display('steadfast'), 'pathao' => $this->display('pathao'), 'redx' => $this->display('redx')]);
    }

    public function update(Request $request, string $provider)
    {
        abort_unless(isset(self::SCHEMAS[$provider]), 404);
        $schema = self::SCHEMAS[$provider];
        $this->authorizeAccess($request, $schema['permission']);
        $row = IntegrationSetting::where('provider', $provider)->firstOrFail();
        $credentials = $row->credentials ?? [];
        $rules = [];
        $attributes = [];
        foreach ($schema['fields'] as $field) {
            $key = $field['key'];
            $secret = $field['type'] === 'password';
            $required = $field['required'] && (! $secret || empty($credentials[$key]));
            $rules[$key] = [$required ? 'required' : 'nullable', 'string', 'max:'.($field['type'] === 'url' ? 2048 : 1000)];
            if ($field['type'] === 'url') {
                $rules[$key][] = 'url:http,https';
            }
            if ($key === 'store_id') $rules[$key][] = 'regex:/^[0-9]+$/';
            if ($field['type'] === 'email') {
                $rules[$key][] = 'email';
            }
            if ($field['type'] === 'number') {
                $rules[$key] = ['required', 'integer', 'between:1,65535'];
            }
            if ($field['type'] === 'select') {
                $rules[$key][] = Rule::in(array_keys($field['options']));
            }
            $attributes[$key] = $field['label'];
        }
        if ($provider === 'sslcommerz') {
            $rules['mode'] = ['required', Rule::in(['test', 'live'])];
            $rules['status'] = 'required|boolean';
        }
        $data = $request->validate($rules, [], $attributes);
        if ($provider === 'sslcommerz' && $data['mode'] === 'live' && $data['store_id'] === 'testbox') {
            throw \Illuminate\Validation\ValidationException::withMessages(['store_id' => 'Demo credentials can only be used in test mode. Enter your approved live merchant credentials.']);
        }
        if (in_array($provider, ['steadfast', 'pathao', 'redx'], true) && !\App\Services\CourierGateway::validBase($provider, $data['base_url'])) {
            throw \Illuminate\Validation\ValidationException::withMessages(['base_url' => 'Use this courier’s official HTTPS API URL.']);
        }
        $settings = $row->settings ?? [];
        foreach ($schema['fields'] as $field) {
            $key = $field['key'];
            if ($field['type'] === 'password') {
                if (isset($data[$key]) && $data[$key] !== '') {
                    $credentials[$key] = $data[$key];
                }
            } else {
                $settings[$key] = $data[$key] ?? null;
            }
        }
        if ($provider === 'sslcommerz') {
            $settings['mode'] = $data['mode'];
            $settings['status'] = (bool) $data['status'];
        }
        $row->update(['settings' => $settings, 'credentials' => $credentials]);

        return back()->with('success', ucfirst($provider).' setup updated successfully.');
    }
}
