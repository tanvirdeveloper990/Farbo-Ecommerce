<script setup>
import { computed } from 'vue';
import { Ticket, Gift, BadgeCheck } from '@lucide/vue';
const props = defineProps({ order: Object, format: Function });
const rows = computed(() => [
    props.order.coupon_code && { label: 'Coupon', code: props.order.coupon_code, amount: props.order.coupon_discount, icon: Ticket },
    props.order.gift_card_code && { label: 'Gift card', code: props.order.gift_card_code, amount: props.order.gift_card_discount, icon: Gift },
    !props.order.coupon_code && !props.order.gift_card_code && Number(props.order.discount_amount) && { label: 'Discount', amount: props.order.discount_amount, icon: Ticket },
].filter(Boolean));
</script>
<template>
    <section v-if="rows.length" class="order-savings" aria-label="Discount breakdown">
        <header class="savings-heading"><BadgeCheck :size="16"/><span>Your savings</span><strong>{{ format(order.discount_amount || 0) }}</strong></header>
        <article v-for="row in rows" :key="row.label" class="savings-line">
            <i class="savings-icon"><component :is="row.icon" :size="17"/></i>
            <p class="savings-copy"><b>{{ row.label }}</b><code v-if="row.code">{{ row.code }}</code></p>
            <strong class="savings-amount">−{{ format(row.amount || 0) }}</strong>
        </article>
    </section>
</template>
<style scoped>
.order-savings{display:grid;width:100%;min-width:0;box-sizing:border-box;flex-basis:100%;border:1px solid #d6e8dd;border-radius:14px;background:#f4faf6;overflow:hidden;margin:6px 0;font-family:inherit;text-align:left}
.savings-heading{display:flex;align-items:center;gap:7px;padding:12px 14px;color:#276446;font-size:12px;font-weight:600;border-bottom:1px solid #e0ece4}.savings-heading strong{margin-left:auto!important;color:#276446!important;font-size:13px;white-space:nowrap}
.savings-line{display:flex;align-items:center;gap:11px;padding:13px 14px;min-width:0}.savings-line+.savings-line{border-top:1px dashed #d8e6dd}.savings-icon{display:grid;place-items:center;flex:0 0 34px;width:34px;height:34px;border-radius:10px;background:#e4f0e8;color:#337a54}.savings-copy{display:grid;gap:4px;flex:1;min-width:0;margin:0}.savings-copy b{font-size:12px;color:#2c4636;font-weight:600}.savings-copy code{font-family:inherit;font-size:11px;color:#62796b;overflow-wrap:anywhere;letter-spacing:.05em}.savings-amount{font-size:14px!important;color:#237447!important;white-space:nowrap;margin:0!important;font-variant-numeric:tabular-nums}
@media(max-width:380px){.savings-line{gap:8px;padding:12px}.savings-heading{padding:12px}.savings-amount{font-size:12px!important}}
</style>
