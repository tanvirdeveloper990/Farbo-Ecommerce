<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogAttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\CustomerReviewController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\IntegrationSettingController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicStorefrontController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\StoreContentController;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;

Route::match(['GET', 'POST'], '/payments/sslcommerz/return/{attempt}/{result}', [\App\Http\Controllers\SslcommerzController::class, 'callback'])
    ->where('attempt', 'RN[0-9a-f-]{36}')->whereIn('result', ['success', 'fail', 'cancel'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, \Illuminate\Session\Middleware\StartSession::class, \Illuminate\View\Middleware\ShareErrorsFromSession::class, \App\Http\Middleware\HandleInertiaRequests::class])->middleware('throttle:30,1')->name('payments.sslcommerz.return');
Route::post('/payments/sslcommerz/ipn', [\App\Http\Controllers\SslcommerzController::class, 'ipn'])
    ->middleware('throttle:60,1')->name('payments.sslcommerz.ipn');
use Illuminate\Support\Facades\Artisan;


// Maintenance routes: super admins only, so visitors cannot run artisan commands.
Route::get('/storage', function (Request $request) {
    abort_unless($request->user()?->is_admin && $request->user()?->is_super_admin, 404);
    Artisan::call('storage:link');

    return 'Done';
})->middleware(['auth', 'throttle:3,1']);







Route::get('/cmd', function (Request $request) {
    abort_unless($request->user()?->is_admin && $request->user()?->is_super_admin, 404);
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('optimize:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');

    return 'Done';
})->middleware(['auth', 'throttle:3,1']);

// Temporary maintenance: uncomment this route and only the commands you need.
// Sign in as a super admin, visit /cmd once, then comment the route again.
// If routes are already cached, clear that cache from the terminal first.
// Route::get('/cmd', function (Request $request) {
//     abort_unless($request->user()?->is_admin && $request->user()?->is_super_admin, 403);
//     $commands = [
//         // 'optimize:clear',
//         // 'cache:clear',
//         // 'route:clear',
//         // 'storage:link',
//     ];
//     $results = [];
//     foreach ($commands as $command) {
//         try {
//             $exitCode = \Illuminate\Support\Facades\Artisan::call($command);
//             $results[] = ['command' => $command, 'successful' => $exitCode === 0];
//         } catch (\Throwable $exception) {
//             report($exception);
//             $results[] = ['command' => $command, 'successful' => false];
//         }
//     }
//     return response()->json(['results' => $results, 'message' => $commands ? 'Maintenance finished.' : 'Uncomment the commands you need.'])
//         ->header('Cache-Control', 'no-store');
// })->middleware(['auth', 'throttle:2,1']);

// Static storefront templates; the existing catalog remains available for development.
Route::get('/', [\App\Http\Controllers\FarboStorefrontController::class, 'home'])->name('home');
Route::get('/popular-categories', [\App\Http\Controllers\FarboStorefrontController::class, 'popularCategories'])->name('popular-categories');
Route::get('/single-page', [\App\Http\Controllers\FarboStorefrontController::class, 'product'])->name('product.static');
Route::redirect('/index.html', '/');
Route::redirect('/single-page.html', '/single-page');

Route::get('/catalog', function (Request $request) {
    Inertia::setRootView('storefront');
    $settings = SiteSetting::current();
    $searchTerm = $request->validate(['q' => 'nullable|string|max:200'])['q'] ?? '';
    $wishlistMode = $request->boolean('wishlist');
    $wishIds = [];
    if ($wishlistMode) {
        $input = $request->validate(['wish_ids' => ['nullable', 'string', 'max:2000', 'regex:/^\d+(,\d+)*$/']]);
        $wishIds = array_slice(array_unique(array_filter(array_map('intval', explode(',', $input['wish_ids'] ?? '')))), 0, 100);
    }

    $products = Product::with(['category:id,name', 'images:id,product_id,path', 'variants.color:id,name', 'variants.size:id,name'])
        ->where('status', true)
        ->when($wishlistMode, fn ($query) => $query->whereIn('id', $wishIds))
        ->when($searchTerm, fn ($query) => $query->where(fn ($query) => $query->where('name', 'like', '%'.$searchTerm.'%')->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$searchTerm.'%'))))
        ->latest('id')
        ->take($wishlistMode ? 100 : 40)
        ->get()
        ->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'category' => strtoupper($product->category?->name ?? 'COLLECTION'),
            'image' => $product->featured_image_one ?: $product->images->first()?->path ?: $product->featured_image_two,
            'price' => (float) ($product->sale_price !== null && $product->sale_price > 0 ? $product->sale_price : $product->regular_price),
            'oldPrice' => $product->sale_price !== null && $product->sale_price > 0 ? (float) $product->regular_price : 0,
            'stock' => $product->stock,
            'newArrival' => $product->new_arrival,
            'description' => trim(preg_replace('/\s+/', ' ', strip_tags($product->description ?? ''))),
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'stock' => $variant->stock,
                'price' => (float) $variant->price,
                'label' => collect([$variant->color?->name, $variant->size?->name])->filter()->implode(' / '),
            ])->values(),
        ]);

    $searchProducts = Product::with(['category:id,name', 'images:id,product_id,path'])
        ->where('status', true)
        ->latest('id')
        ->get(['id', 'name', 'category_id', 'featured_image_one', 'featured_image_two', 'regular_price', 'sale_price'])
        ->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'category' => strtoupper($product->category?->name ?? 'COLLECTION'),
            'image' => $product->featured_image_one ?: $product->images->first()?->path ?: $product->featured_image_two,
            'price' => (float) ($product->sale_price !== null && $product->sale_price > 0 ? $product->sale_price : $product->regular_price),
        ]);

    $newArrivals = Product::with(['category:id,name', 'images:id,product_id,path', 'variants.color:id,name', 'variants.size:id,name'])
        ->where('status', true)
        ->where('new_arrival', true)
        ->latest('updated_at')
        ->latest('id')
        ->get()
        ->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'category' => strtoupper($product->category?->name ?? 'COLLECTION'),
            'image' => $product->featured_image_one ?: $product->images->first()?->path ?: $product->featured_image_two,
            'price' => (float) ($product->sale_price !== null && $product->sale_price > 0 ? $product->sale_price : $product->regular_price),
            'oldPrice' => $product->sale_price !== null && $product->sale_price > 0 ? (float) $product->regular_price : 0,
            'stock' => $product->stock,
            'newArrival' => true,
            'description' => trim(preg_replace('/\s+/', ' ', strip_tags($product->description ?? ''))),
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'stock' => $variant->stock,
                'price' => (float) $variant->price,
                'label' => collect([$variant->color?->name, $variant->size?->name])->filter()->implode(' / '),
            ])->values(),
        ]);

    $settings['shipping_inside_dhaka'] ??= 80;
    $settings['shipping_outside_dhaka'] ??= 150;

    $categories = Category::with('subcategories:id,category_id,name')
        ->where('is_active', true)
        ->where('show_on_home', true)
        ->orderBy('serial')
        ->orderBy('id')
        ->get(['id', 'name', 'slug', 'image']);
    $categorySections = $categories->map(fn (Category $category) => [
        'id' => $category->id,
        'slug' => $category->slug,
        'name' => $category->name,
        'products' => Product::with(['category:id,name', 'images:id,product_id,path', 'variants.color:id,name', 'variants.size:id,name'])
            ->where('category_id', $category->id)
            ->where('status', true)
            ->latest('id')
            ->take(8)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'category' => strtoupper($category->name),
                'image' => $product->featured_image_one ?: $product->images->first()?->path ?: $product->featured_image_two,
                'price' => (float) ($product->sale_price !== null && $product->sale_price > 0 ? $product->sale_price : $product->regular_price),
                'oldPrice' => $product->sale_price !== null && $product->sale_price > 0 ? (float) $product->regular_price : 0,
                'stock' => $product->stock,
                'newArrival' => $product->new_arrival,
                'variants' => $product->variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'stock' => $variant->stock,
                    'price' => (float) $variant->price,
                    'label' => collect([$variant->color?->name, $variant->size?->name])->filter()->implode(' / '),
                ])->values(),
            ]),
    ])->values();
    $popularCategories = Category::where('is_active', true)->where('is_popular', true)
        ->orderBy('serial')
        ->orderBy('id')
        ->get(['id', 'name', 'slug', 'image']);

    return Inertia::render('Storefront/Home', [
        'wishlistMode' => $wishlistMode,
        'newArrivals' => $newArrivals,
        'products' => $products,
        'searchProducts' => $searchProducts,
        'searchTerm' => $searchTerm,
        'categorySections' => $categorySections,
        'popularCategories' => $popularCategories,
        'banners' => Banner::orderBy('serial')->orderBy('id')->get(),
        'settings' => $settings,
    ]);
})->name('catalog');
Route::get('/policies/{policy:slug}', [PolicyController::class, 'show']);
Route::get('/about-us', [PolicyController::class, 'about'])->name('about-us');
Route::get('/career', [\App\Http\Controllers\CareerController::class, 'page'])->name('career');
Route::post('/career', [\App\Http\Controllers\CareerController::class, 'store'])->middleware('throttle:5,1');
Route::get('/founder-message', [\App\Http\Controllers\FounderMessageController::class, 'show'])->name('founder-message');
Route::get('/contact', [ContactController::class, 'page'])->name('contact.page');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:8,1')->name('contact.store');
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/discounts', [\App\Http\Controllers\DiscountCodeController::class, 'preview'])->middleware('throttle:30,1');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');
Route::get('/order-success/{order}', [CheckoutController::class, 'success'])->middleware(\App\Http\Middleware\CleanOrderConfirmation::class)->whereNumber('order')->name('orders.success');
Route::get('/order-success/{order}/invoice', [CheckoutController::class, 'invoice'])->middleware('signed')->whereNumber('order')->name('orders.invoice');
Route::get('/how-to-buy', function () { Inertia::setRootView('storefront'); return Inertia::render('Storefront/HowToBuy'); })->name('how-to-buy');

Route::get('/track-order', [\App\Http\Controllers\OrderTrackingController::class, 'index'])->middleware('throttle:30,1')->name('track-order');

Route::get('/new-arrivals', [PublicStorefrontController::class, 'newArrivals'])->name('new-arrivals');
Route::get('/customer-reviews', [PublicStorefrontController::class, 'reviews'])->name('customer-reviews');

Route::get('/categories', [PublicStorefrontController::class, 'category']);
Route::get('/categories/{category}', [PublicStorefrontController::class, 'category']);
Route::get('/product/{product}', [\App\Http\Controllers\FarboStorefrontController::class, 'product'])->whereNumber('product');
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        Inertia::setRootView('storefront');

        return Inertia::render('Auth', ['mode' => 'login', 'admin' => false]);
    })->name('login');
    Route::get('/admin/login', fn () => Inertia::render('AdminLogin', ['branding' => \Illuminate\Support\Arr::only(SiteSetting::current(), ['header_logo', 'company_name'])]))->name('admin.login');
    Route::get('/register', function () {
        Inertia::setRootView('storefront');

        return Inertia::render('Auth', ['mode' => 'register', 'admin' => false]);
    });
    Route::get('/regiser', fn () => redirect('/register'));
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect('/my-account'));
    Route::get('/my-account', [CustomerAccountController::class, 'profile']);
    Route::get('/profile', fn () => request()->user()->is_admin
        ? Inertia::render('Profile', ['admin' => true])
        : redirect('/my-account'));
    Route::get('/my-orders', [CustomerAccountController::class, 'orders']);
    Route::get('/my-orders/{order}', [CustomerAccountController::class, 'showOrder'])->whereNumber('order');
    Route::get('/change-password', fn () => request()->user()->is_admin
        ? Inertia::render('Profile', ['admin' => true])
        : (function () { Inertia::setRootView('storefront'); return Inertia::render('CustomerPassword'); })());
    Route::patch('/profile', [AuthController::class, 'profile']);
    Route::put('/password', [AuthController::class, 'password'])->middleware('throttle:6,1');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/', fn () => redirect('/admin/dashboard'));
        Route::get('/dashboard', function () {
            $user = request()->user();
            $canViewOrders = $user->hasPermission('orders.manage');
            $canViewProducts = $user->hasPermission('products.manage');
            $canViewUsers = $user->hasPermission('users.manage');
            $canViewRoles = $user->hasPermission('roles.manage');
            $canViewPermissions = $user->hasPermission('permissions.manage');
            $days = collect(range(6, 0))->map(function (int $offset) use ($canViewOrders) {
                $date = Carbon::today()->subDays($offset);
                $orders = $canViewOrders
                    ? Order::whereDate('created_at', $date)->selectRaw(
                        'COUNT(*) as count, COALESCE(SUM(CASE WHEN status = ? THEN total ELSE 0 END), 0) as revenue',
                        ['completed'],
                    )->first()
                    : null;

                return [
                    'date' => $date->format('M j'),
                    'orders' => (int) ($orders->count ?? 0),
                    'revenue' => (float) ($orders->revenue ?? 0),
                ];
            });

            return Inertia::render('Dashboard', [
                'stats' => [
                    'customers' => $canViewUsers ? User::where('is_admin', false)->count() : null,
                    'team' => $canViewUsers ? User::where('is_admin', true)->count() : null,
                    'roles' => $canViewRoles ? Role::count() : null,
                    'permissions' => $canViewPermissions ? Permission::count() : null,
                    'orders' => $canViewOrders ? Order::count() : null,
                    'pending' => $canViewOrders ? Order::where('status', 'pending')->count() : null,
                    'completed' => $canViewOrders ? Order::where('status', 'completed')->count() : null,
                    'revenue' => $canViewOrders ? (float) Order::where('status', 'completed')->sum('total') : null,
                    'products' => $canViewProducts ? Product::count() : null,
                ],
                'chart' => [
                    'canViewOrders' => $canViewOrders,
                    'days' => $days,
                ],
                'recentOrders' => $canViewOrders
                    ? Order::latest('id')->take(8)->get(['id', 'customer_name', 'status', 'total', 'created_at'])
                    : [],
            ]);
        });
        Route::get('/profile', fn () => Inertia::render('Profile', ['admin' => true]));
        Route::get('/customers', [\App\Http\Controllers\AdminCustomerController::class, 'index']);
        Route::get('/customers/{kind}/{id}', [\App\Http\Controllers\AdminCustomerController::class, 'show'])->whereIn('kind', ['account', 'guest', 'order'])->whereNumber('id');
        Route::get('/management/{type}', [ManagementController::class, 'index'])->whereIn('type', ['users', 'roles', 'permissions']);
        Route::post('/management/{type}', [ManagementController::class, 'save'])->whereIn('type', ['users', 'roles', 'permissions']);
        Route::put('/management/{type}/{id}', [ManagementController::class, 'save'])->whereIn('type', ['users', 'roles', 'permissions']);
        Route::delete('/management/{type}/{id}', [ManagementController::class, 'destroy'])->whereIn('type', ['users', 'roles', 'permissions']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/create', [CategoryController::class, 'form']);
        Route::get('/categories/records/{type}/{id}/edit', [CategoryController::class, 'formRecord'])->whereIn('type', ['category', 'subcategory', 'child'])->whereNumber('id');
        Route::post('/categories/records', [CategoryController::class, 'storeRecord']);
        Route::post('/categories/records/{type}/{id}', [CategoryController::class, 'updateRecord'])->whereIn('type', ['category', 'subcategory', 'child'])->whereNumber('id');
        Route::delete('/categories/records/{type}/{id}', [CategoryController::class, 'destroyRecord'])->whereIn('type', ['category', 'subcategory', 'child'])->whereNumber('id');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'form']);
        Route::post('/categories', [CategoryController::class, 'save']);
        Route::post('/categories/move', [CategoryController::class, 'moveHierarchy']);
        Route::post('/categories/reorder', [CategoryController::class, 'reorder']);
        Route::post('/categories/{category}', [CategoryController::class, 'save']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/create', [ProductController::class, 'form']);
        Route::get('/products/{product}/edit', [ProductController::class, 'form']);
        Route::post('/products', [ProductController::class, 'save']);
        Route::post('/products/{product}', [ProductController::class, 'save']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::get('/{type}', [CatalogAttributeController::class, 'index'])->whereIn('type', ['brands', 'colors', 'sizes']);
        Route::post('/{type}', [CatalogAttributeController::class, 'store'])->whereIn('type', ['brands', 'colors', 'sizes']);
        Route::put('/{type}/{attribute}', [CatalogAttributeController::class, 'update'])->whereIn('type', ['brands', 'colors', 'sizes']);
        Route::delete('/{type}/{attribute}', [CatalogAttributeController::class, 'destroy'])->whereIn('type', ['brands', 'colors', 'sizes']);
        Route::get('/policies/{policy:slug}', [PolicyController::class, 'edit']);
        Route::put('/policies/{policy:slug}', [PolicyController::class, 'update']);
        Route::get('/customer-reviews', [CustomerReviewController::class, 'index']);
        Route::get('/customer-reviews/create', [CustomerReviewController::class, 'form']);
        Route::get('/customer-reviews/{review}/edit', [CustomerReviewController::class, 'form']);
        Route::post('/customer-reviews', [CustomerReviewController::class, 'save']);
        Route::post('/customer-reviews/{review}', [CustomerReviewController::class, 'save']);
        Route::delete('/customer-reviews/{review}', [CustomerReviewController::class, 'destroy']);
        Route::get('/banners', [StoreContentController::class, 'banners']);
        Route::post('/banners', [StoreContentController::class, 'saveBanner']);
        Route::post('/banners/{banner}', [StoreContentController::class, 'saveBanner']);
        Route::delete('/banners/{banner}', [StoreContentController::class, 'deleteBanner']);
        Route::get('/contacts', [ContactController::class, 'index']);
        Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->whereNumber('contact');
        Route::get('/contacts/{contact}', [ContactController::class, 'show'])->whereNumber('contact');
        Route::put('/contacts/{contact}', [ContactController::class, 'update'])->whereNumber('contact');
        Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->whereNumber('contact');
        Route::get('/smtp', [IntegrationSettingController::class, 'smtp']);
        Route::put('/smtp', [IntegrationSettingController::class, 'update'])->defaults('provider', 'smtp');
        Route::get('/payments', [IntegrationSettingController::class, 'payments']);
        Route::put('/payments/sslcommerz', [IntegrationSettingController::class, 'update'])->defaults('provider', 'sslcommerz');
        Route::post('/courier/{provider}/test', [\App\Http\Controllers\CourierController::class, 'connection'])->whereIn('provider', ['steadfast', 'pathao', 'redx'])->middleware('throttle:6,1');
        Route::get('/courier/redx/areas', [\App\Http\Controllers\CourierController::class, 'areas'])->middleware('throttle:20,1');
        Route::post('/orders/{order}/courier', [\App\Http\Controllers\CourierController::class, 'book'])->whereNumber('order')->middleware('throttle:10,1');
        Route::post('/orders/{order}/courier/sync', [\App\Http\Controllers\CourierController::class, 'sync'])->whereNumber('order')->middleware('throttle:20,1');
        Route::post('/orders/{order}/courier/attach', [\App\Http\Controllers\CourierController::class, 'attach'])->whereNumber('order')->middleware('throttle:6,1');
        foreach (['coupons' => 'coupon', 'gift-cards' => 'gift_card'] as $path => $type) {
            Route::get('/'.$path, [\App\Http\Controllers\DiscountCodeController::class, 'index'])->defaults('type', $type);
            Route::get('/'.$path.'/create', [\App\Http\Controllers\DiscountCodeController::class, 'form'])->defaults('type', $type);
            Route::get('/'.$path.'/{discount}/edit', [\App\Http\Controllers\DiscountCodeController::class, 'form'])->defaults('type', $type)->whereNumber('discount');
            Route::post('/'.$path, [\App\Http\Controllers\DiscountCodeController::class, 'save'])->defaults('type', $type);
            Route::put('/'.$path.'/{discount}', [\App\Http\Controllers\DiscountCodeController::class, 'save'])->defaults('type', $type)->whereNumber('discount');
            Route::delete('/'.$path.'/{discount}', [\App\Http\Controllers\DiscountCodeController::class, 'destroy'])->defaults('type', $type)->whereNumber('discount');
        }
        Route::get('/courier', [IntegrationSettingController::class, 'couriers']);
        Route::put('/courier/{provider}', [IntegrationSettingController::class, 'update'])->whereIn('provider', ['steadfast', 'pathao', 'redx']);
        Route::get('/faqs', [FaqController::class, 'index']);
        Route::get('/faqs/create', [FaqController::class, 'form']);
        Route::get('/faqs/{faq}/edit', [FaqController::class, 'form']);
        Route::post('/faqs', [FaqController::class, 'save']);
        Route::put('/faqs/{faq}', [FaqController::class, 'save']);
        Route::delete('/faqs/{faq}', [FaqController::class, 'destroy']);
        Route::get('/settings', [StoreContentController::class, 'settings']);
        Route::get('/why-choose-us', [StoreContentController::class, 'whyChooseUs']);
        Route::get('/about-us', [PolicyController::class, 'editAbout']);
        Route::get('/career', [\App\Http\Controllers\CareerController::class, 'index']);
        Route::get('/career/{careerApplication}/attachment', [\App\Http\Controllers\CareerController::class, 'attachment'])->whereNumber('careerApplication');
        Route::get('/career/{careerApplication}', [\App\Http\Controllers\CareerController::class, 'show'])->whereNumber('careerApplication');
        Route::get('/founder-messages', [\App\Http\Controllers\FounderMessageController::class, 'index']);
        Route::get('/founder-messages/create', [\App\Http\Controllers\FounderMessageController::class, 'form']);
        Route::get('/founder-messages/{founderMessage}/edit', [\App\Http\Controllers\FounderMessageController::class, 'form'])->whereNumber('founderMessage');
        Route::post('/founder-messages/reorder', [\App\Http\Controllers\FounderMessageController::class, 'reorder']);
        Route::post('/founder-messages', [\App\Http\Controllers\FounderMessageController::class, 'save']);
        Route::post('/founder-messages/{founderMessage}', [\App\Http\Controllers\FounderMessageController::class, 'save'])->whereNumber('founderMessage');
        Route::delete('/founder-messages/{founderMessage}', [\App\Http\Controllers\FounderMessageController::class, 'destroy'])->whereNumber('founderMessage');
        Route::post('/why-choose-us', [StoreContentController::class, 'saveWhyChooseUs']);
        Route::post('/settings', [StoreContentController::class, 'saveSettings']);
        Route::get('/media', [MediaController::class, 'index']);
        Route::get('/media/library', [MediaController::class, 'library']);
        Route::post('/media', [MediaController::class, 'store']);
        Route::post('/media/import-storefront', [MediaController::class, 'importStorefront']);
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->whereNumber('media');
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}/invoice', function (Request $request, Order $order) {
            abort_unless($request->user()->hasPermission('orders.manage'), 403);
            return app(CheckoutController::class)->invoice($order);
        })->whereNumber('order');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->whereNumber('order');
        Route::put('/orders/{order}', [OrderController::class, 'update'])->whereNumber('order');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->whereNumber('order');
        Route::get('/shipping', [ShippingController::class, 'index']);
        Route::put('/shipping', [ShippingController::class, 'update']);
        Route::get('/{module}', function (string $module) {
            $modules = ['categories' => 'Categories', 'courier' => 'Courier setup', 'smtp' => 'SMTP setup', 'media' => 'Media', 'pages' => 'Pages', 'blogs' => 'Blogs', 'testimonials' => 'Testimonials', 'payments' => 'Payments', 'contacts' => 'Contacts', 'faqs' => 'FAQs', 'landing-page' => 'Landing page', 'settings' => 'Settings'];
            abort_unless(isset($modules[$module]), 404);

            return Inertia::render('Module', ['title' => $modules[$module], 'module' => $module]);
        })->whereIn('module', ['categories', 'courier', 'smtp', 'media', 'pages', 'blogs', 'testimonials', 'payments', 'contacts', 'faqs', 'landing-page', 'settings']);
    });
});





