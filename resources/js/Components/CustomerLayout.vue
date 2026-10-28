<script setup>
import { computed, ref, nextTick, watch, onMounted, onBeforeUnmount } from "vue";
import { customerSidebarHighlight } from './customerSidebarHighlight';
import { Link, usePage } from "@inertiajs/vue3";
import { KeyRound, LogOut, ShoppingBag, UserRound } from "@lucide/vue";
import StorefrontLayout from "../Storefront/StorefrontLayout.vue";

const page = usePage();
const user = computed(() => page.props.auth.user);
const initials = computed(() =>
    (user.value?.name || "")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join("")
        .toUpperCase(),
);
const activePath = computed(() => page.url.split("?")[0]);
const navigation = ref(null);
const highlight = ref(null);
let firstFrame;
let secondFrame;
const highlightStyle = computed(() => highlight.value ? {
    transform: `translate(${highlight.value.left}px, ${highlight.value.top}px)`,
    width: `${highlight.value.width}px`, height: `${highlight.value.height}px`,
} : {});
function alignHighlight() {
    const nav = navigation.value;
    const active = nav?.querySelector('a.active');
    if (!active) { highlight.value = null; return; }
    const bounds = nav.getBoundingClientRect();
    const target = active.getBoundingClientRect();
    const rect = { left: target.left - bounds.left, top: target.top - bounds.top, width: target.width, height: target.height };
    highlight.value = rect;
    customerSidebarHighlight.rect = rect;
    customerSidebarHighlight.userId = user.value.id;
}
onMounted(() => {
    if (customerSidebarHighlight.userId === user.value.id && customerSidebarHighlight.rect) {
        highlight.value = customerSidebarHighlight.rect;
        firstFrame = requestAnimationFrame(() => { secondFrame = requestAnimationFrame(alignHighlight); });
    } else alignHighlight();
    window.addEventListener('resize', alignHighlight);
});
watch(activePath, () => nextTick(alignHighlight));
onBeforeUnmount(() => {
    cancelAnimationFrame(firstFrame);
    cancelAnimationFrame(secondFrame);
    window.removeEventListener('resize', alignHighlight);
});
</script>

<template>
    <StorefrontLayout>
        <section class="customer-account-shell">
            <aside class="customer-sidebar">
                <div class="customer-identity">
                    <span class="customer-avatar">{{ initials }}</span>
                    <div class="customer-identity-copy">
                        <strong>{{ user.name }}</strong>
                        <span>{{ user.phone || "Phone not added" }}</span>
                    </div>
                </div>
                <nav ref="navigation" class="customer-account-nav" aria-label="Customer account">
                    <span v-if="highlight" class="customer-active-highlight" :style="highlightStyle" aria-hidden="true"></span>
                    <Link
                        href="/my-account"
                        :class="{ active: activePath === '/my-account' }"
                    >
                        <UserRound :size="18" /> My account
                    </Link>
                    <Link
                        href="/my-orders"
                        :class="{
                            active:
                                activePath === '/my-orders' ||
                                activePath.startsWith('/my-orders/'),
                        }"
                    >
                        <ShoppingBag :size="18" /> My orders
                    </Link>
                    <Link
                        href="/change-password"
                        :class="{ active: activePath === '/change-password' }"
                    >
                        <KeyRound :size="18" /> Change password
                    </Link>
                    <Link href="/logout" method="post" as="button">
                        <LogOut :size="18" /> Logout
                    </Link>
                </nav>
            </aside>
            <div class="customer-account-content"><slot /></div>
        </section>
    </StorefrontLayout>
</template>

<style scoped>
.customer-account-content :deep(.field){display:grid;gap:8px}.customer-account-content :deep(.field>label){font-size:12px;font-weight:600;color:#0B2A5B}.customer-account-content :deep(input:not([type=checkbox])),.customer-account-content :deep(textarea){width:100%;border:1px solid #dce4ef;border-radius:9px;background:#fff;padding:12px 14px;color:#0B2A5B;font-size:13px;outline:none;transition:border-color .2s,box-shadow .2s}.customer-account-content :deep(input:focus),.customer-account-content :deep(textarea:focus){border-color:#123A75;box-shadow:0 0 0 3px #E3061309}.customer-account-content :deep(.field-error){color:#b42318;font-size:11px}.customer-account-content :deep(.field-hint){color:#64748b;font-size:11px}.customer-account-content :deep(.password-input){position:relative}.customer-account-content :deep(.password-input input){padding-right:45px}.customer-account-content :deep(.password-input button){position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#64748b;background:transparent;border:0;display:flex}.customer-sidebar{position:sticky;top:140px!important;border-radius:18px!important}.customer-account-shell{background:#F3F6FB}.customer-account-nav .active{box-shadow:inset 3px 0 #E30613;font-weight:650}.customer-account-nav a,.customer-account-nav button{border:0;text-decoration:none;text-align:left}.customer-account-content :deep(button:disabled){opacity:.6;cursor:wait}@media(max-width:760px){.customer-sidebar{position:static}}
.customer-account-shell {
    display: grid;
    width: min(1280px, 100%);
    min-height: 560px;
    grid-template-columns: 270px minmax(0, 1fr);
    gap: 32px;
    margin: 0 auto;
    padding: 42px 28px 70px;
}
.customer-sidebar {
    align-self: start;
    border: 1px solid #dce4ef;
    border-radius: 14px;
    background: #fff;
    padding: 22px 17px 14px;
    box-shadow: 0 8px 30px #0B2A5B0a;
}
.customer-identity {
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #dce4ef;
    padding: 0 3px 20px;
}
.customer-avatar {
    display: grid;
    width: 52px;
    height: 52px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 50%;
    background: linear-gradient(145deg, #E30613, #123A75);
    color: white;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.04em;
}
.customer-identity-copy {
    display: grid;
    min-width: 0;
    gap: 5px;
}
.customer-identity-copy strong {
    overflow: hidden;
    color: #0B2A5B;
    font-size: 14px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.customer-identity-copy span {
    overflow: hidden;
    color: #64748b;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.customer-account-nav {
    display: grid;
    gap: 5px;
    padding-top: 14px;
}
.customer-account-nav a,
.customer-account-nav button {
    display: flex;
    min-height: 43px;
    align-items: center;
    gap: 11px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    padding: 0 11px;
    color: #0B2A5B;
    font-size: 12px;
    text-align: left;
    transition:
        background-color 150ms ease,
        color 150ms ease;
}
.customer-account-nav a:hover,
.customer-account-nav button:hover,
.customer-account-nav .active {
    background: #F3F6FB;
    color: #E30613;
}
.customer-account-nav button {
    width: 100%;
    cursor: pointer;
}
.customer-account-content {
    min-width: 0;
}
@media (max-width: 760px) {
    .customer-account-shell {
        grid-template-columns: 1fr;
        gap: 20px;
        padding: 24px 16px 48px;
    }
    .customer-sidebar {
        padding: 15px;
    }
    .customer-identity {
        padding-bottom: 13px;
    }
    .customer-account-nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        padding-top: 10px;
    }
    .customer-account-nav a,
    .customer-account-nav button {
        font-size: 11px;
    }
}
.customer-account-nav{position:relative;isolation:isolate}.customer-active-highlight{position:absolute;left:0;top:0;z-index:-1;pointer-events:none;border-radius:8px;background:#F3F6FB;box-shadow:inset 3px 0 #E30613;transition:transform 340ms cubic-bezier(.22,.68,0,1.05),width 340ms ease,height 340ms ease;will-change:transform}.customer-account-nav .active{background:transparent;box-shadow:none}.customer-account-nav a,.customer-account-nav button{position:relative}.customer-account-nav a:hover:not(.active),.customer-account-nav button:hover{background:#F3F6FB80}@media(prefers-reduced-motion:reduce){.customer-active-highlight{transition:none}}
</style>
