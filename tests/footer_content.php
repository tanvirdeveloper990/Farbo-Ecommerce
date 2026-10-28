<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
DB::beginTransaction();
try {
    $admin = App\Models\User::where('is_super_admin', true)->firstOrFail();
    $request = Request::create('/admin/settings', 'POST', ['company_name' => App\Models\SiteSetting::current()['company_name'], 'footer_description' => 'Temporary footer verification text', 'topbar_text' => 'Temporary topbar text', 'topbar_highlight' => 'Temporary highlight']);
    $request->setUserResolver(fn () => $admin);
    $request->setLaravelSession($app->make('session.store'));
    app(App\Http\Controllers\StoreContentController::class)->saveSettings($request, app(App\Services\MediaLibrary::class));
    $html = view('farbo.partials.footer', ['farboSettings' => App\Models\SiteSetting::current()])->render();
    if (!str_contains($html, 'Temporary footer verification text') || str_contains($html, '> Brands</a>') || str_contains($html, '> Sourcing Services</a>')) throw new RuntimeException('Footer content or links are incorrect.');
    if (!str_contains($html, '/about-us') || !str_contains($html, '/categories')) throw new RuntimeException('Footer destinations are incorrect.');
    echo "PASS: settings description renders dynamically and footer destinations are correct.\n";
    $header = view('farbo.partials.header', ['farboSettings' => App\Models\SiteSetting::current(), 'farboAccountUrl' => '/login'])->render();
    if (!str_contains($header, 'Temporary topbar text') || !str_contains($header, '<span class="text-brand-red">Temporary highlight</span>')) throw new RuntimeException('Topbar settings did not render.');
    echo "PASS: main topbar text and highlighted text render from Settings.\n";
    $policy = App\Models\Policy::where('slug', 'about-us')->firstOrFail();
    $request = Request::create('/admin/policies/about-us', 'PUT', ['content' => '<p><strong>Temporary About verification</strong></p><script>alert(1)</script>']);
    $request->setUserResolver(fn () => $admin);
    $request->setLaravelSession($app->make('session.store'));
    app(App\Http\Controllers\PolicyController::class)->update($request, $policy);
    $response = app(App\Http\Controllers\PolicyController::class)->about();
    $props = (new ReflectionProperty($response, 'props'))->getValue($response);
    if (!str_contains($props['policy']->content, '<strong>Temporary About verification</strong>') || str_contains($props['policy']->content, '<script')) throw new RuntimeException('About rich content did not save safely.');
    echo "PASS: About Us Summernote content saves and is supplied to its standalone page.\n";
} finally { DB::rollBack(); }
echo "PASS: verification data rolled back.\n";
