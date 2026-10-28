# Meta Pixel and Google Tag Manager setup

Admin > Settings > Analytics & conversion tracking:
- Enter your numeric Meta Pixel ID and enable Meta Pixel.
- Enter your web GTM container ID (GTM-XXXXXXX) and enable Google Tag Manager.
- Save settings. Leave an ID blank or disable its switch to stop the corresponding integration.
- No invented account IDs have been installed. Real account delivery cannot be checked until your IDs and published tags are available.

Events:
| Action | Meta Pixel | GTM dataLayer event |
|---|---|---|
| Home/category/product/checkout/success navigation | PageView | page_view |
| Home/category/new arrival product lists | Category: ViewCategory (custom event) | view_item_list |
| Single product | ViewContent | view_item |
| Successful add/increase quantity | AddToCart | add_to_cart |
| Remove/decrease quantity | — | remove_from_cart |
| Checkout with nonempty cart | InitiateCheckout | begin_checkout |
| Submit payment choice | AddPaymentInfo | add_payment_info |
| New COD order / verified online payment | Purchase | purchase |

Currency is BDT. Product IDs match the catalog database IDs. Meta Purchase value includes delivery; GA4 purchase value is the product subtotal with delivery in shipping. Order transaction_id matches the receipt number (10000 + database order ID). Failed/unpaid online payments and cancelled orders produce no Purchase. Old receipts do not become new purchases. Per-order, per-destination browser storage suppresses duplicates across refresh; blocked storage falls back to in-memory suppression. Browser tracking is best-effort and can be blocked by ad blockers or cleared storage; this is not server-side CAPI.

GTM / Google Analytics setup in your account:
1. Create a Google tag with your GA4 Measurement ID (G-...). Disable its automatic page-view sending (send_page_view=false) and enhanced measurement history-change page views so the explicit page_view event is not duplicated.
2. Add a Custom Event trigger with regex: ^(page_view|view_item_list|view_item|add_to_cart|remove_from_cart|begin_checkout|add_payment_info|purchase)$
3. Create a GA4 Event tag using Event Name {{Event}}, enable Send Ecommerce data using Data Layer, and attach this trigger. Use the GA4 Google tag from step 1.
4. Add Data Layer variables page_location, page_path, page_title and map them to the corresponding parameters on the GA4 Event tag.
5. Preview with Tag Assistant, navigate through the store, then publish the container. A GTM container ID alone does not automatically create GA4 tags.
6. Use Meta Events Manager > Test Events and Meta Pixel Helper to check your real Pixel. Keep duplicate Meta tags in GTM disabled when using the direct Meta switch in this app.

Customer names, email, phone and address are excluded from custom event payloads. Admin screens do not emit these storefront events. SDK scripts load once across Inertia navigation. Signed order access URLs are exchanged for a session-protected clean confirmation URL before analytics scripts load. Invoice downloads keep their signed access protection.

Verified locally: 23 tracking event tests, 3 admin settings validation checks, 5 confirmation access checks, 17 checkout checks, 18 payment checks, 107 routes and production build. Mock tests do not send fixture data to external accounts. Temporary database fixtures were rolled back.

Files: resources/js/Storefront/tracking.js; tests/tracking.mjs; tests/tracking_settings.php; tests/tracking_order_access.php.

Official references:
- Google ecommerce: https://developers.google.com/analytics/devguides/collection/ga4/ecommerce
- Google data layer: https://developers.google.com/tag-platform/tag-manager/datalayer
- Meta Pixel conversion events: https://developers.facebook.com/docs/meta-pixel/implementation/conversion-tracking/
