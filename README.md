# Nest Commerce

Laravel 12 + Inertia 2 + Vue 3 admin and customer workspace.

## Start locally

Requires PHP 8.2+, Composer, Node.js and a configured database.

```sh
composer install
npm ci
# For a new checkout, copy .env.example to .env and configure the database.
php artisan key:generate
php artisan migrate --seed
php artisan admin:create admin@example.com --name="Store Owner"
npm run build
php artisan serve --port=8017
```

The admin command prompts for a password; no predictable owner password is seeded. Do not regenerate APP_KEY on an existing deployed installation. For frontend development, run `npm run dev` alongside the PHP server.

## Routes

- `/`: Rupnagar storefront based on the supplied design.
- `/admin/login`: administrator login.
- `/admin/dashboard`: administrator dashboard with real account totals.
- `/admin/profile`: profile and password management.
- `/admin/management/users`, `/roles`, `/permissions`: searchable, paginated CRUD.
- `/login`, `/register`: customer authentication. `/regiser` redirects to `/register`.
- `/dashboard`, `/profile`: separate customer experience.

All requested commerce, content, order, payment and settings sections are navigable UI previews. Their business logic is intentionally left for the next phase. There are no fabricated sales or order metrics.

## Access model

Customers cannot enter admin routes. Admins without a management permission cannot open or mutate that management resource. The store owner can manage all accounts, roles and permissions. Delegated `users.manage` administrators can manage customers, but cannot grant or edit admin access. `roles.manage` and `permissions.manage` allow delegated read access; their write actions remain owner-only to prevent privilege escalation. Custom permission keys can be assigned to roles for future modules; they do not automatically protect preview routes.

The owner account cannot be deleted/demoted, users cannot remove their own access, assigned roles cannot be deleted, and core permission keys cannot be renamed/deleted. Registration/profile endpoints never accept privilege fields. Passwords are hashed, authentication endpoints are rate limited, sessions rotate at login and logout, and Laravel CSRF protection applies to all mutations.

## Verification

```sh
php artisan test
npm run build
```

Tests use a separate in-memory SQLite database and cover authentication, customer/admin isolation, privilege injection, CRUD, permissions, owner safeguards, validation, passwords and all preview routes.

## Storefront

The reusable Vue master is `resources/js/Storefront/StorefrontLayout.vue`, with shared header, navigation, footer and product rail components. The root uses `resources/views/storefront.blade.php` and a separate Vite entry to keep storefront styling independent from account/admin pages. Logo, banners and supplied CSS load from `public/assets`.

Catalog items in `resources/js/Storefront/catalog.js` are static demo data. Search, wishlist, quick view and cart work locally in the page; cart/wishlist reset on reload and checkout does not place orders. Category links lead to collections. Policy/social destinations await real content and links. Remote photos and contact text remain from the supplied design.

## Dynamic storefront content

- `/admin/banners`: image upload, edit, serial ordering and delete confirmation. Lower serial numbers appear first; ties use the banner ID. JPG/PNG/WebP up to 5 MB.
- `/admin/settings`: company name, two phones/emails, address, Maps URL, copyright, header/footer logos, favicon, marquee and Facebook/Instagram/YouTube/TikTok links. Blank social links are hidden. Logos support replacement/removal (2 MB maximum).
- Owner access is automatic; other admins need `banners.manage` or `settings.manage` assigned through roles.
- Migration seeds the two supplied banner images and existing company details without changing user accounts. Social URLs are intentionally empty until real account URLs are provided.
- Run `php artisan migrate` and `php artisan storage:link` on deployment. Uploads live under `storage/app/public/storefront`; supplied assets are never deleted by banner management.
- The catalog/cart remain demo data; banners and company settings now come from the database.

The header announcement is fixed: “Welcome to” + company name + one editable announcement field. Settings also has one Shop image upload above Unstitched; replacement/removal uses the same validated image flow. The existing promotional image is seeded as its initial value.

## Category management

`/admin/categories` provides category CRUD, drag-and-drop serial ordering, keyboard/mobile up/down controls (including page boundaries), and 40 records per page. Create/edit uses one nested form with optional main-category image, multiple subcategories and multiple children. Category, Subcategory and ChildCategory have separate tables/models. Nested updates preserve IDs and use a database transaction; removing a branch deletes its descendants. Only parent categories support images. Owners have access; delegated admins need `categories.manage`. Other commerce modules remain previews.

## Legal and policy pages

Settings is followed by the Legal & policy sidebar dropdown. Each of the five fixed pages has a Summernote editor and update action at `/admin/policies/{slug}`; public footer links open `/policies/{slug}`. Content is sanitized server-side. Owners or admins with `policies.manage` can update these pages. Policy text starts empty for the store to supply. Header uses phone/email one; footer Contact us uses phone/email two and no Maps link.

## Customer reviews

`/admin/customer-reviews` provides paginated create/edit/delete for customer name, optional portrait (JPG/PNG/WebP, 2 MB), 1–5 stars, description and active/inactive status. Save returns to the list. Owners have access; delegated administrators require `reviews.manage`. Only active reviews are returned to the storefront. Footer Customer Reviews links to the responsive, swipeable review section with rating summary and expandable descriptions. No fabricated reviews are seeded. Upload replacement/deletion removes only this feature's stored images.

## Payment, courier and FAQ setup

`/admin/payments` contains one SSLcommerz update form for Store ID, password, Test/Live mode and status. `/admin/courier` contains independent Steadfast, Pathao and REDX update forms on one page. These are fixed provider records with no create/delete list. Credentials are encrypted using the application's key, never returned to forms, and excluded from validation flash data. Leave a saved secret blank to retain it. Keep the application key safe and stable so saved credentials remain readable. These screens store configuration only; checkout and shipment API calls are not implemented here.

`/admin/faqs` provides question, answer and active/inactive status CRUD with 20 records per page, validation and delete confirmation. Save returns to the list. Owners have access automatically; delegated administrators need `payments.manage`, `couriers.manage` or `faqs.manage` for the corresponding section. Deployment requires `php artisan migrate` and `npm run build`.

## SMTP setup

/admin/smtp provides one Update SMTP form with mailer, host, port, username, password, optional None/TLS/SSL encryption, sender address and sender name. Owners or administrators with smtp.manage can update it. Passwords are encrypted, omitted from responses and validation flash data, and retained when left blank on subsequent updates. This stores configuration only; application mail delivery is not switched to these settings yet.

