<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Only presence flags are reported; credentials and account identifiers stay private.
$required = [
    'sslcommerz' => ['store_id', 'store_password'],
    'steadfast' => ['base_url', 'api_key', 'secret_key'],
    'pathao' => ['base_url', 'client_id', 'client_secret', 'client_email', 'client_password'],
    'redx' => ['base_url', 'store_id', 'api_token'],
    'smtp' => ['host', 'port', 'username', 'smtp_password', 'from_address'],
];
$results = [];
foreach ($required as $provider => $fields) {
    $row = App\Models\IntegrationSetting::where('provider', $provider)->first();
    $values = array_merge($row?->settings ?? [], $row?->credentials ?? []);
    $results[$provider] = [
        'record_exists' => (bool) $row,
        'missing_fields' => array_values(array_filter($fields, fn ($field) => empty($values[$field]))),
    ];
    if ($provider === 'sslcommerz') {
        $results[$provider]['enabled'] = (bool) ($values['status'] ?? false);
        $results[$provider]['mode'] = $values['mode'] ?? 'unset';
    }
}
$results['runtime_email'] = [
    'mailer' => config('mail.default'),
    'smtp_username_present' => ! empty(config('mail.mailers.smtp.username')),
    'smtp_password_present' => ! empty(config('mail.mailers.smtp.password')),
];
file_put_contents(storage_path('app/external-integrations-audit.json'), json_encode($results, JSON_PRETTY_PRINT));
echo json_encode($results, JSON_PRETTY_PRINT).PHP_EOL;
