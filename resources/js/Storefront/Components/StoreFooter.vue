<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { inject } from "vue";
import { Link } from "@inertiajs/vue3";
const settings = computed(
    () => usePage().props.storeSettings || usePage().props.settings || {},
);
const categories = computed(() => (usePage().props.footerCategories || []).slice(0, 5));
const hasSocial = computed(() =>
    ["facebook", "instagram", "youtube", "tiktok"].some(
        (key) => settings.value[key],
    ),
);
const { notify } = inject("storefront");
function followLink(event) {
    const link = event.target.closest("a");
    if (!link || link.getAttribute("href") !== "/#collections") return;
    const label = link.getAttribute("aria-label") || link.textContent.trim();
    event.preventDefault();
    if (label === "Contact Us")
        document
            .getElementById("contact")
            .scrollIntoView({ behavior: "smooth" });
    else notify(label + " will be available soon.");
}
</script>
<template>
    <footer @click="followLink" id="contact" class="bg-black text-white">
        <div class="mx-auto max-w-7xl px-4 pb-8 pt-14 sm:pt-16 lg:pt-20">
            <div
                class="grid grid-cols-2 gap-x-6 gap-y-12 md:grid-cols-3 lg:grid-cols-12 lg:gap-x-8"
            >
                <div class="col-span-2 md:col-span-3 lg:col-span-3">
                    <Link href="/" class="inline-block">
                        <img
                            :src="settings.footer_logo"
                            v-if="settings.footer_logo"
                            :alt="settings.company_name"
                            class="h-16 w-auto object-contain sm:h-20"
                        /><span v-else class="font-serif text-2xl">{{
                            settings.company_name
                        }}</span></Link>
                    <p
                        class="mt-5 max-w-xs text-sm leading-relaxed text-white/60"
                    >
                        Unstitched, ready to wear and jewelry for every season
                        and celebration, delivered across Bangladesh.
                    </p>

                    <p
                        v-if="hasSocial"
                        class="mt-7 text-xs font-semibold uppercase tracking-[0.2em]"
                    >
                        Follow us
                    </p>
                    <div v-if="hasSocial" class="mt-4 flex gap-3">
                        <a
                            :href="settings.facebook"
                            v-if="settings.facebook"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Facebook"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 transition duration-300 hover:border-white hover:bg-white hover:text-black"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.5V4.4c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.4H8v3h2.6V21h2.9z"
                                />
                            </svg>
                        </a>
                        <a
                            :href="settings.instagram"
                            v-if="settings.instagram"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="Instagram"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 transition duration-300 hover:border-white hover:bg-white hover:text-black"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <rect
                                    x="3"
                                    y="3"
                                    width="18"
                                    height="18"
                                    rx="5"
                                />
                                <circle cx="12" cy="12" r="4" />
                                <circle
                                    cx="17.5"
                                    cy="6.5"
                                    r="1"
                                    fill="currentColor"
                                    stroke="none"
                                />
                            </svg>
                        </a>
                        <a
                            :href="settings.youtube"
                            v-if="settings.youtube"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="YouTube"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 transition duration-300 hover:border-white hover:bg-white hover:text-black"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M21.6 7.2a2.5 2.5 0 00-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 002.4 7.2 26 26 0 002 12a26 26 0 00.4 4.8 2.5 2.5 0 001.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 001.8-1.8A26 26 0 0022 12a26 26 0 00-.4-4.8zM10 15V9l5.2 3L10 15z"
                                />
                            </svg>
                        </a>
                        <a
                            :href="settings.tiktok"
                            v-if="settings.tiktok"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="TikTok"
                            class="flex h-10 w-10 items-center justify-center rounded-full border border-white/25 transition duration-300 hover:border-white hover:bg-white hover:text-black"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    d="M16.6 5.8A4.3 4.3 0 0115.5 3h-3.1v12.4a2.6 2.6 0 11-2.6-2.6c.3 0 .5 0 .8.1V9.7a5.7 5.7 0 105 5.7V9.1a7.4 7.4 0 004.3 1.4V7.4a4.3 4.3 0 01-3.3-1.6z"
                                />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <h3
                        class="text-sm font-semibold uppercase tracking-[0.18em]"
                    >
                        Top categories
                    </h3>
                    <span class="mt-3 block h-px w-10 bg-white"></span>
                    <ul class="mt-6 space-y-3.5 text-sm text-white/65">
                        <li v-for="category in categories" :key="category.id">
                            <Link
                                :href="`/categories/${encodeURIComponent(category.slug || category.id)}`"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                            >
                                <span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span>
                                {{ category.name }}
                            </Link>
                        </li>
                        <li v-if="!categories.length" class="text-white/50">
                            Categories coming soon
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <h3
                        class="text-sm font-semibold uppercase tracking-[0.18em]"
                    >
                        Quick links
                    </h3>
                    <span class="mt-3 block h-px w-10 bg-white"></span>
                    <ul class="mt-6 space-y-3.5 text-sm text-white/65">
                        <li>
                            <Link
                                href="/policies/about-us"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >About Us</Link
                            >
                        </li>
                        <li>
                            <Link
                                href="/how-to-buy"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >How to Buy</Link>
                        </li>
                        <li>
                            <Link
                                href="/customer-reviews"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Customer Reviews</Link>
                        </li>
                        <li>
                            <Link
                                href="/track-order"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Track Your Order</Link>
                        </li>
                        <li>
                            <Link
                                href="/contact"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Contact Us</Link
                            >
                        </li>
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <h3
                        class="text-sm font-semibold uppercase tracking-[0.18em]"
                    >
                        Legal &amp; policy
                    </h3>
                    <span class="mt-3 block h-px w-10 bg-white"></span>
                    <ul class="mt-6 space-y-3.5 text-sm text-white/65">
                        <li>
                            <Link
                                href="/policies/delivery-policy"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Delivery Policy</Link>
                        </li>
                        <li>
                            <Link
                                href="/policies/return-policy"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Return Policy</Link>
                        </li>
                        <li>
                            <Link
                                href="/policies/refund-policy"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Refund Policy</Link>
                        </li>
                        <li>
                            <Link
                                href="/policies/terms-and-conditions"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Terms &amp; Conditions</Link>
                        </li>
                        <li>
                            <Link
                                href="/policies/privacy-policy"
                                class="group inline-flex items-center gap-2 transition hover:text-white"
                                ><span
                                    class="h-px w-0 bg-white transition-all duration-300 group-hover:w-3"
                                ></span
                                >Privacy Policy</Link>
                        </li>
                    </ul>
                </div>

                <div class="col-span-2 md:col-span-3 lg:col-span-3">
                    <h3
                        class="text-sm font-semibold uppercase tracking-[0.18em]"
                    >
                        Contact us
                    </h3>
                    <span class="mt-3 block h-px w-10 bg-white"></span>
                    <ul class="mt-6 space-y-5 text-sm">
                        <li v-if="settings.phone_two">
                            <p class="mb-2 text-xs uppercase text-white/50">
                                Phone
                            </p>
                            <a
                                v-for="key in ['phone_two'].filter(
                                    (k) => settings[k],
                                )"
                                :key="key"
                                :href="'tel:' + settings[key]"
                                class="block mb-1"
                                >{{ settings[key] }}</a
                            >
                        </li>
                        <li v-if="settings.email_two">
                            <p class="mb-2 text-xs uppercase text-white/50">
                                Email
                            </p>
                            <a
                                v-for="key in ['email_two'].filter(
                                    (k) => settings[k],
                                )"
                                :key="key"
                                :href="'mailto:' + settings[key]"
                                class="block mb-1 break-all"
                                >{{ settings[key] }}</a
                            >
                        </li>
                        <li v-if="settings.address">
                            <p class="mb-2 text-xs uppercase text-white/50">
                                Address
                            </p>
                            <p class="whitespace-pre-line leading-relaxed">
                                {{ settings.address }}
                            </p>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-14 flex flex-col items-center justify-between gap-5 border-t border-white/15 pt-7 text-center md:flex-row md:text-left"
            >
                <p class="text-xs text-white/55 sm:text-sm">
                    {{ settings.copyright }}
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <span
                        class="mr-1 text-[11px] uppercase tracking-[0.15em] text-white/45"
                        >We accept</span
                    >
                    <span
                        class="rounded border border-white/20 px-2.5 py-1 text-[11px] font-semibold tracking-wide"
                        >bKash</span
                    >
                    <span
                        class="rounded border border-white/20 px-2.5 py-1 text-[11px] font-semibold tracking-wide"
                        >Nagad</span
                    >
                    <span
                        class="rounded border border-white/20 px-2.5 py-1 text-[11px] font-semibold tracking-wide"
                        >VISA</span
                    >
                    <span
                        class="rounded border border-white/20 px-2.5 py-1 text-[11px] font-semibold tracking-wide"
                        >Mastercard</span
                    >
                    <span
                        class="rounded border border-white/20 px-2.5 py-1 text-[11px] font-semibold tracking-wide"
                        >COD</span
                    >
                </div>
            </div>
        </div>
    </footer>

    <a
        :href="'https://wa.me/' + settings.phone_one?.replace(/\D/g, '')"
        v-if="settings.phone_one"
        target="_blank"
        rel="noopener"
        aria-label="Chat on WhatsApp"
        class="group fixed bottom-5 right-5 z-40 flex items-center sm:bottom-6 sm:right-6"
    >
        <span
            class="mr-3 hidden translate-x-2 rounded-full bg-white px-4 py-2 text-sm font-medium shadow-lg opacity-0 transition group-hover:translate-x-0 group-hover:opacity-100 md:block"
        >
            Chat with us
        </span>
        <span
            class="wa-float relative flex h-14 w-14 items-center justify-center"
        >
            <span
                class="absolute inset-0 animate-ping rounded-full bg-[#25D366] opacity-50"
            ></span>
            <span
                class="relative flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] shadow-xl shadow-green-900/20 transition group-hover:scale-110"
            >
                <svg
                    class="wa-wiggle h-7 w-7 text-white"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"
                    />
                </svg>
            </span>
        </span>
    </a>
</template>
