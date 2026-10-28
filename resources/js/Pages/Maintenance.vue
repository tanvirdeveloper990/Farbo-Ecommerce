<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { Sprout, ArrowRight, Wrench, ArrowUpRight } from "@lucide/vue";
</script>
<template>
    <Head title="Something good is growing" />
    <div class="maintenance">
        <header>
            <Link href="/" class="brand"
                ><span class="brand-mark"><Sprout /></span>nest.</Link
            ><Link href="/login" class="text-link"
                >My account <ArrowUpRight :size="17"
            /></Link>
        </header>
        <main>
            <span class="pill"
                ><span class="live-dot"></span> A LITTLE WORK BEHIND THE
                SCENES</span
            >
            <div class="maintenance-art">
                <div class="halo"></div>
                <div class="plant-pot">
                    <Sprout :size="112" stroke-width="1" />
                </div>
                <span class="tool-bubble"><Wrench :size="26" /></span>
            </div>
            <h1>Something good<br />is <em>growing.</em></h1>
            <p>
                We’re giving our store a little extra love.<br />A refreshed
                experience is on its way. See you soon.
            </p>
            <Link href="/login" class="btn primary"
                >Visit your account <ArrowRight :size="18" /></Link
            ><small
                >Your account is still right here, whenever you need it.</small
            >
        </main>
        <footer>
            <span>© {{ new Date().getFullYear() }} Nest Commerce</span
            ><span>Thoughtfully improving, just for you.</span
            ><Link href="/admin/login"
                >Admin access <ArrowUpRight :size="14"
            /></Link>
        </footer>
    </div>
</template>
