<?php

// Isolated regression checks; never connects to the application's MySQL database.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
Illuminate\Support\Facades\DB::purge('sqlite');

use App\Http\Controllers\CategoryController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Category;
use App\Models\User;
use App\Services\MediaLibrary;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

foreach (['categories', 'subcategories', 'child_categories'] as $name) {
    Schema::create($name, function (Blueprint $table) use ($name) {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('image')->nullable();
        $table->integer('serial')->default(1);
        $table->boolean('is_active')->default(true);
        if ($name === 'categories') {
            $table->boolean('show_in_header')->default(true);
            $table->boolean('show_on_home')->default(true);
            $table->boolean('show_on_sidebar')->default(true);
            $table->boolean('is_popular')->default(false);
        } else {
            $table->integer($name === 'subcategories' ? 'category_id' : 'subcategory_id');
        }
        $table->timestamps();
    });
}
$user = new User;
$user->is_admin = true;
$user->is_super_admin = true;
function requestFor(array $data, string $url = '/admin/categories/records'): Request
{
    global $app, $user;
    $request = Request::create($url, 'POST', $data);
    $request->setLaravelSession($app['session.store']);
    $app->instance('request', $request);
    $request->setUserResolver(fn () => $user);
    return $request;
}
function check(bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
    echo "PASS: $message\n";
}
$controller = new CategoryController;
$library = new MediaLibrary;
$controller->storeRecord(requestFor(['type' => 'category', 'name' => 'Visibility', 'is_active' => true, 'show_in_header' => false, 'show_on_home' => false, 'show_on_sidebar' => true, 'is_popular' => true]), $library);
$category = Category::firstOrFail();
check($category->is_popular, 'Popular Category switch persists On when creating');
check(! $category->show_in_header && ! $category->show_on_home && $category->show_on_sidebar, 'Create saves independent visibility switches');
$controller->updateRecord(requestFor(['name' => 'Visibility', 'is_active' => true, 'show_in_header' => true, 'show_on_home' => true, 'show_on_sidebar' => false, 'is_popular' => false]), 'category', $category->id, $library);
$category->refresh();
check(! $category->is_popular, 'Popular Category switch persists Off when editing');
check($category->show_in_header && $category->show_on_home && ! $category->show_on_sidebar, 'Edit saves On and Off values');
$controller->storeRecord(requestFor(['type' => 'subcategory', 'parent_id' => $category->id, 'name' => 'Sub', 'is_active' => true, 'show_on_sidebar' => false]), $library);
check(App\Models\Subcategory::count() === 1, 'Subcategory creation accepts hidden category options without storing them');
$controller->storeRecord(requestFor(['type' => 'child', 'parent_id' => App\Models\Subcategory::first()->id, 'name' => 'Child', 'is_active' => true]), $library);
check(App\Models\ChildCategory::count() === 1, 'Child category creation retains existing behavior');
for ($i = 2; $i <= 12; $i++) Category::create(['name' => 'Category '.$i, 'serial' => $i]);
$last = Category::orderByDesc('serial')->first();
$controller->reorder(requestFor(['id' => $last->id, 'target_id' => $category->id, 'position' => 'before']));
check(Category::orderBy('serial')->first()->id === $last->id, 'Drag reorder persists the new category order');
$request = Request::create('/');
$request->setLaravelSession($app['session.store']);
$shared = (new HandleInertiaRequests)->share($request);
check($shared['storeCategories']()->count() === 12, 'Header receives all enabled categories for the 10 plus More menu');
check(! $shared['sidebarCategories']()->contains('id', $category->id), 'Sidebar independently excludes disabled categories');
$footer = $shared['footerCategories']();
check($footer->count() === 5 && $footer->first()->id === $last->id, 'Footer returns exactly the first five categories in saved order');
$last->update(['is_active' => false]);
check(! $shared['storeCategories']()->contains('id', $last->id) && ! $shared['footerCategories']()->contains('id', $last->id), 'Inactive categories are excluded from navigation and footer');
