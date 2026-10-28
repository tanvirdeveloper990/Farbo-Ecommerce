# Courier integration setup

The admin order page supports Steadfast, Pathao and REDX booking, consignment storage, manual status refresh and uncertain-request reconciliation. Checkout does not create courier bookings automatically.

Admin > Courier setup: save the provider credentials, then click Test connection. Tests use read-only balance/store-list endpoints and do not create parcels.

- Steadfast: HTTPS API base URL, API Key and Secret Key. Default: https://portal.steadfast.com.bd/api/v1
- Pathao: https://api-hermes.pathao.com, pickup store ID, client ID/secret, merchant email/password. Sandbox URL: https://courier-api-sandbox.pathao.com. Full delivery address must include locality and district; Pathao resolves the location automatically.
- REDX: https://openapi.redx.com.bd/v1.0.0-beta, pickup store ID and API token. Pickup store ID differs from shop ID. Load and select the recipient delivery area before booking.

Admin > Orders > selected order > Courier delivery: select provider, verify parcel weight and delivery details, then Book courier. Recipient details are sent only when this action is used. Paid orders have zero collection amount; unpaid COD orders use the server order total. Unpaid online orders cannot be booked.

Refresh courier status reads the provider API and stores the raw status. Delivered parcels mark the order completed; supported transit statuses mark it on the way. Returns and partial deliveries are held for manual review without automatic stock restoration. Delivery does not mark COD remittance paid.

One shipment record per order prevents duplicate bookings. Network timeout, malformed responses and ambiguous gateway errors leave the booking unknown and block automatic retries. Check the merchant portal; link an existing consignment using its ID and confirm it belongs to the order. Stalled creating requests can be linked after two minutes. This verifies the ID/status remotely; ownership correspondence must be checked in the portal. Definite rejected requests can be corrected and retried.

Changing the order status to cancelled does not cancel a remote courier parcel: use the courier merchant portal. Orders with shipment records cannot be deleted through the admin action. Live webhooks and automatic background polling are not included; use Refresh courier status.

Deployment: run php artisan migrate --force for courier_shipments. Vendor and production assets are already available locally; deploy the updated application/build. Existing courier secrets remain encrypted and are never exposed in the frontend. HTTPS host validation restricts credential transmission to supported official courier hosts.

Verification: 27 courier mock checks, 15 public tracking checks, checkout/inventory functional checks, 107 route checks and successful production build. Test database records were rolled back. No real credentials are configured, so real account authentication and parcel acceptance are not yet verified. Steadfast/REDX merchant documentation was inaccessible through public browsing; verify the enabled account API contract with the provider when testing live credentials.

Official Pathao address-resolution reference: https://pathao.com/bn/blog/api-merchant-auto-address-feature/
