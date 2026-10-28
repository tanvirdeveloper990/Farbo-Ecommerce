<script setup>
import { Head, Link, usePage } from "@inertiajs/vue3";
import {
    UserRound,
    ShieldCheck,
    ArrowUpRight,
    ShoppingBag,
    Package,
    Heart,
} from "@lucide/vue";
import CustomerLayout from "../Components/CustomerLayout.vue";
const page = usePage();
</script>
<template>
    <Head title="Your little corner" /><CustomerLayout
        ><div class="customer-greeting">
            <span class="eyebrow">YOUR LITTLE CORNER OF NEST</span>
            <h1>Hello, {{ page.props.auth.user.name.split(" ")[0] }}.</h1>
            <p>Make yourself at home. Everything you need is right here.</p>
        </div>
        <section class="customer-welcome">
            <div>
                <span class="pill">A FRESH CHAPTER</span>
                <h2>Good things are<br />on their way.</h2>
                <p>
                    We’re putting the finishing touches on our store.<br />In
                    the meantime, make your account feel like you.
                </p>
                <Link href="/profile" class="btn primary"
                    >Complete your profile <ArrowUpRight :size="17"
                /></Link>
            </div>
            <ShoppingBag class="customer-bag" :size="170" stroke-width=".8" />
        </section>
        <div class="customer-cards">
            <Link href="/profile" class="panel customer-action"
                ><span class="empty-icon"><UserRound /></span>
                <h3>Your details</h3>
                <p>A little about you. Keep your information up to date.</p>
                <span class="text-link"
                    >Edit profile <ArrowUpRight :size="16" /></span></Link
            ><Link href="/profile#password" class="panel customer-action"
                ><span class="empty-icon"><ShieldCheck /></span>
                <h3>Peace of mind</h3>
                <p>Keep your account secure with a strong password.</p>
                <span class="text-link"
                    >Change password <ArrowUpRight :size="16" /></span
            ></Link>
            <div class="panel customer-action">
                <span class="empty-icon"><Package /></span>
                <h3>Your orders</h3>
                <p>
                    Your order history will be available when our store opens.
                </p>
                <span class="soft-badge">COMING SOON</span>
            </div>
        </div>
        <p class="customer-note">
            <Heart :size="16" /> Thanks for being here from the beginning.
        </p></CustomerLayout
    >
</template>
