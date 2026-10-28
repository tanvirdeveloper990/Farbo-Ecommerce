<?php

namespace App\Services;

use App\Models\{Banner, Category, CustomerReview, Product, ProductImage, ProductVariant, SiteSetting};
use Illuminate\Support\Facades\{DB, Http, Storage};

class StorefrontMediaImporter
{
    public function run(): array
    {
        $library = app(MediaLibrary::class);
        $settings = SiteSetting::current();
        unset($settings['shop_image']);
        $settings['footer_background'] ??= 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=1600&q=70';
        $settings['product_video_poster'] ??= 'https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=1200&h=675&q=70';
        SiteSetting::updateOrCreate(['id' => 1], ['data' => $settings]);
        $columns = [[Product::class, 'featured_image_one'], [Product::class, 'featured_image_two'], [ProductImage::class, 'path'], [ProductVariant::class, 'image_path'], [Category::class, 'image'], [Banner::class, 'image'], [CustomerReview::class, 'image']];
        $settingsKeys = ['header_logo', 'footer_logo', 'favicon', 'why_image', 'footer_background', 'product_video_poster'];
        $urls = ['/farbo/assets/img/logo/logo.jpeg'];
        $names = ['/farbo/assets/img/logo/logo.jpeg' => 'FARBO Logo'];
        foreach ($columns as [$model, $column]) array_push($urls, ...$model::whereNotNull($column)->pluck($column)->all());
        foreach (Product::all() as $product) foreach (['featured_image_one', 'featured_image_two'] as $column) if ($product->{$column}) $names[$product->{$column}] ??= $product->name;
        foreach (Category::whereNotNull('image')->get() as $category) $names[$category->image] ??= $category->name;
        foreach (Banner::orderBy('serial')->get() as $i => $banner) $names[$banner->image] ??= 'FARBO Banner '.($i + 1);
        foreach ($settingsKeys as $key) if (!empty($settings[$key])) $names[$settings[$key]] ??= ucwords(str_replace('_', ' ', $key));
        foreach ($settingsKeys as $key) if (!empty($settings[$key])) $urls[] = $settings[$key];
        $report = ['imported' => [], 'failed' => []];
        foreach (array_unique(array_filter($urls)) as $url) {
            try {
                $existing = $library->storagePath($url);
                if ($existing && Storage::disk('public')->exists($existing)) {
                    $asset = $library->register($existing, ($names[$url] ?? 'FARBO Website Image').'.'.pathinfo($existing, PATHINFO_EXTENSION));
                } else {
                    $path = 'media/frontend/'.hash('sha256', $url).'.jpg';
                    if (!Storage::disk('public')->exists($path)) {
                        if (parse_url($url, PHP_URL_HOST)) {
                            if (parse_url($url, PHP_URL_SCHEME) !== 'https' || !in_array(strtolower(parse_url($url, PHP_URL_HOST)), ['images.unsplash.com'], true)) throw new \RuntimeException('Image host is not supported for automatic import.');
                            $response = Http::timeout(20)->withOptions(['allow_redirects' => false])->get($url);
                            $response->throw();
                            $bytes = $response->body();
                        } else {
                            $root = realpath(public_path());
                            $source = realpath(public_path(ltrim(parse_url($url, PHP_URL_PATH), '/')));
                            if (!$source || !str_starts_with(strtolower($source), strtolower($root).DIRECTORY_SEPARATOR)) throw new \RuntimeException('Local image does not exist.');
                            $bytes = file_get_contents($source);
                        }
                        if (strlen($bytes) > 20 * 1024 * 1024 || !@getimagesizefromstring($bytes)) throw new \RuntimeException('Source is not a supported image.');
                        if (!Storage::disk('public')->put($path, $bytes)) throw new \RuntimeException('Could not store image.');
                    }
                    $asset = $library->register($path, ($names[$url] ?? 'FARBO Website Image').'.jpg');
                }
                $replacement = '/storage/'.$asset->path;
                DB::transaction(function () use ($columns, $url, $replacement, $settingsKeys) {
                    foreach ($columns as [$model, $column]) $model::where($column, $url)->update([$column => $replacement]);
                    $current = SiteSetting::current();
                    foreach ($settingsKeys as $key) if (($current[$key] ?? null) === $url) $current[$key] = $replacement;
                    SiteSetting::updateOrCreate(['id' => 1], ['data' => $current]);
                });
                $report['imported'][$url] = $replacement;
            } catch (\Throwable $exception) {
                $report['failed'][$url] = $exception->getMessage();
            }
        }
        return $report;
    }
}
