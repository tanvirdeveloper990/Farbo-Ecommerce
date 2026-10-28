<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\CatalogAttribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FarboStorefrontSeeder extends Seeder
{
    public function run(): void
    {
        $source = json_decode(file_get_contents(__DIR__.'/data/farbo-storefront.json'), true, flags: JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($source) {
            foreach ($source['menu'] as $serial => $menu) {
                $category = Category::firstOrCreate(['slug' => Str::slug($menu['name'])], [
                    'name' => $menu['name'], 'serial' => $serial, 'is_active' => true,
                    'show_in_header' => true, 'show_on_sidebar' => true, 'show_on_home' => true, 'is_popular' => true,
                ]);
                foreach ($menu['children'] as $position => $name) {
                    $category->subcategories()->firstOrCreate(['slug' => Str::slug($category->slug.' '.$name)], ['name' => $name, 'is_active' => true, 'serial' => $position]);
                }
            }
            foreach ($source['categories'] as $serial => $row) {
                $category = Category::firstOrCreate(['slug' => Str::slug($row['name'])], [
                    'name' => $row['name'], 'image' => $row['image'], 'serial' => $serial + 5,
                    'is_active' => true, 'is_popular' => true, 'show_on_home' => true,
                    'show_in_header' => false, 'show_on_sidebar' => true,
                ]);
                if (!$category->image) $category->update(['image' => $row['image']]);
            }
            foreach ($source['banners'] as $serial => $row) Banner::firstOrCreate(['image' => $row['image']], ['serial' => $serial]);
            $generator = Category::where('slug', 'generator-parts')->firstOrFail();
            $brands = collect(['Original', 'China', 'Local'])->mapWithKeys(fn ($name) => [$name => CatalogAttribute::firstOrCreate(['type' => 'brands', 'name' => $name], ['is_active' => true])]);
            foreach ($source['products'] as $row) {
                Product::firstOrCreate(['name' => $row['name'], 'brand_id' => $brands['Original']->id], [
                    'category_id' => $generator->id, 'regular_price' => $row['price'], 'unit' => 'pcs', 'stock' => 50,
                    'featured_image_one' => $row['image'], 'description' => '<p>Quality '.$row['name'].' for reliable industrial performance.</p>', 'status' => true,
                ]);
            }
            foreach (['Original' => 320, 'China' => 210, 'Local' => 150] as $name => $price) {
                Product::firstOrCreate(['name' => 'Heavy Duty Alternator', 'brand_id' => $brands[$name]->id], [
                    'category_id' => $generator->id, 'regular_price' => $price, 'unit' => 'pcs', 'stock' => 50, 'status' => true,
                    'featured_image_one' => 'https://images.unsplash.com/photo-1595787142842-7404bc60470d?auto=format&fit=crop&w=900&h=900&q=80',
                    'description' => '<p>The FARBO Heavy Duty Alternator (24V 100A) is engineered for reliable and efficient power generation. Choose the brand that suits your requirements.</p>',
                ]);
            }
            $settings = SiteSetting::firstOrNew(['id' => 1]);
            $settings->data = array_merge($settings->data ?? [], [
                'phone_one' => '+8801927123456', 'phone_two' => '+8801923456789',
                'email_one' => 'info@farbo.com', 'email_two' => 'info@farbo.com',
                'address' => 'House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh',
                'footer_description' => 'Your trusted partner for industrial supply, generator parts, tools, hardware and global sourcing. We connect quality products with businesses worldwide.',
                'facebook' => 'https://www.facebook.com/login/', 'linkedin' => 'https://www.linkedin.com/login',
                'youtube' => 'https://accounts.google.com/ServiceLogin?service=youtube', 'whatsapp' => 'https://wa.me/8801927123456',
                'why_title' => 'Reliable Products, Trusted Sourcing, Global Supply',
                'why_summary' => 'FARBO delivers dependable industrial products, efficient sourcing solutions, and responsive trading support to help businesses meet their supply needs with confidence.',
                'why_image' => 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=1400&h=1000&q=80',
            ]);
            $settings->save();
        });
    }
}
