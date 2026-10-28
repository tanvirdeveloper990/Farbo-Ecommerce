<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rules\Password;
Artisan::command('website:sync-footer-content', function () {
    $settings = \App\Models\SiteSetting::current();
    $settings['topbar_text'] ??= 'Everything You Need,';
    $settings['topbar_highlight'] ??= 'One Place';
    $settings['footer_description'] ??= 'Your trusted partner for industrial supply, generator parts, tools, hardware and global sourcing. We connect quality products with businesses worldwide.';
    \App\Models\SiteSetting::updateOrCreate(['id' => 1], ['data' => $settings]);
    $about = \App\Models\Policy::firstOrCreate(['slug' => 'about-us'], ['title' => 'About Us']);
    if (!trim(strip_tags($about->content ?? ''))) $about->update(['content' => '<h2>Industrial products. Practical sourcing. Connected supply.</h2><p>FARBO brings together generator parts, engineering products, industrial supplies and sourcing support for businesses. Our website helps you explore products and contact our team about your requirements.</p><h3>What we offer</h3><ul><li>Generator parts and related equipment.</li><li>Engineering products, tools and industrial supplies.</li><li>Product enquiries and sourcing support.</li></ul><h3>Choosing the right product</h3><p>Before placing an order, review the product model, brand and specifications. If you need help checking compatibility or availability, share your requirements with our team through the Contact Us page.</p><h3>Get in touch</h3><p>Browse our product categories or contact us to discuss the parts and supplies you need.</p>']);
    $this->info('Footer description and About Us content are ready. Existing content has been preserved.');
});
Artisan::command('media:import-storefront', function () {
    $report = app(\App\Services\StorefrontMediaImporter::class)->run();
    foreach ($report['failed'] as $url => $message) $this->warn($url.': '.$message);
    $this->info(count($report['imported']).' website images registered in Media.');
    return $report['failed'] ? 1 : 0;
});

Artisan::command('admin:create {email} {--name=Store Owner}', function () {
    $email = $this->argument('email');
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Enter a valid email.');

        return 1;
    }
    if (User::where('email', $email)->exists()) {
        $this->error('This email already exists.');

        return 1;
    }
    $password = $this->secret('Password (8+ characters, uppercase, lowercase and number)');
    $validator = validator(['password' => $password], ['password' => ['required', Password::min(8)->mixedCase()->numbers()]]);
    if ($validator->fails()) {
        $this->error($validator->errors()->first());

        return 1;
    }
    $user = new User;
    $user->forceFill(['name' => $this->option('name'), 'email' => $email, 'password' => $password, 'is_admin' => true, 'is_super_admin' => true])->save();
    $this->info('Store owner created: '.$email);
})->purpose('Create a store owner securely');
