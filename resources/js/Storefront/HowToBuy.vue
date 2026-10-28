<script setup>
import { Head, Link } from '@inertiajs/vue3';
import StorefrontLayout from './StorefrontLayout.vue';
const steps = [
    { title: 'Choose your product', text: 'Browse a category or search for a product. Open its page to check the details, price and available options.' },
    { title: 'Add it to your cart', text: 'Select a size or color when available, choose the quantity, and press Add to cart. Use Order now to continue directly to checkout.' },
    { title: 'Enter your delivery details', text: 'At checkout, enter your name, phone number and full address, then choose your delivery area. Review the delivery charge and total.' },
    { title: 'Confirm your order', text: 'Choose your payment method, review the policies and confirm the order. Save your order number or download the invoice from the confirmation page.' },
    { title: 'Follow your order', text: 'Enter your order number on Track Your Order to check its status. If you sign in before checkout, the order also appears under My orders.' },
];
</script>
<template>
    <Head title="How to Buy" />
    <StorefrontLayout>
        <section class="mx-auto max-w-4xl px-5 py-12 sm:py-16">
            <p class="mb-3 text-xs uppercase tracking-[0.2em] text-muted">Shopping made simple</p>
            <h1 class="mb-9 font-serif text-4xl sm:text-5xl">How to Buy</h1>
            <ol class="space-y-5">
                <li v-for="(step, index) in steps" :key="step.title" class="flex gap-5 rounded-xl border border-stone p-5 sm:p-6">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blush text-sm font-semibold text-maroon">{{ index + 1 }}</span>
                    <div><h2 class="mb-2 text-base font-semibold">{{ step.title }}</h2><p class="text-sm leading-relaxed text-muted">{{ step.text }}</p></div>
                </li>
            </ol>
            <div class="mt-9 flex flex-wrap gap-5 text-sm"><Link href="/" class="font-semibold text-maroon">Start shopping →</Link><Link href="/track-order" class="text-muted">Track your order</Link><Link href="/contact" class="text-muted">Need help? Contact us</Link></div>
        </section>
    </StorefrontLayout>
</template>
