<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { MapPin, Save, Truck } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";

const props = defineProps({ charges: Object });
const form = useForm({
    inside_dhaka: props.charges.inside_dhaka,
    outside_dhaka: props.charges.outside_dhaka,
});
function save() {
    form.put("/admin/shipping", { preserveScroll: true });
}
</script>

<template>
    <Head title="Shipping charges" />
    <AdminLayout title="Shipping">
        <div class="page-heading">
            <div>
                <span class="eyebrow">DELIVERY CONFIGURATION</span>
                <h1>Shipping charges</h1>
                <p>Set the delivery fee customers see and pay at checkout.</p>
            </div>
        </div>

        <form class="shipping-form" @submit.prevent="save">
            <section class="panel form-panel">
                <div class="form-panel-title">
                    <Truck />
                    <div>
                        <h3>Delivery areas</h3>
                        <p>Set one delivery charge for each checkout area.</p>
                    </div>
                </div>
                <div class="shipping-grid">
                    <Field
                        id="inside_dhaka"
                        label="Inside Dhaka (৳)"
                        :error="form.errors.inside_dhaka"
                        hint="Charged for delivery within Dhaka."
                    >
                        <div class="shipping-input">
                            <MapPin :size="17" />
                            <input
                                id="inside_dhaka"
                                v-model="form.inside_dhaka"
                                type="number"
                                min="0"
                                max="999999.99"
                                step="0.01"
                                required
                            />
                        </div>
                    </Field>
                    <Field
                        id="outside_dhaka"
                        label="Outside Dhaka (৳)"
                        :error="form.errors.outside_dhaka"
                        hint="Charged for delivery outside Dhaka."
                    >
                        <div class="shipping-input">
                            <MapPin :size="17" />
                            <input
                                id="outside_dhaka"
                                v-model="form.outside_dhaka"
                                type="number"
                                min="0"
                                max="999999.99"
                                step="0.01"
                                required
                            />
                        </div>
                    </Field>
                </div>
            </section>
            <div class="shipping-actions">
                <p>Saved changes apply to new checkouts immediately.</p>
                <button
                    class="btn primary"
                    type="submit"
                    :disabled="form.processing"
                >
                    <Save :size="16" />
                    {{ form.processing ? "Saving…" : "Save charges" }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.shipping-form {
    max-width: 860px;
}
.shipping-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}
.shipping-input {
    position: relative;
    display: flex;
    align-items: center;
}
.shipping-input svg {
    position: absolute;
    left: 12px;
    color: #738178;
}
.shipping-input input {
    width: 100%;
    padding-left: 39px;
}
.shipping-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-top: 18px;
}
.shipping-actions p {
    color: #78857a;
    font-size: 12px;
}
@media (max-width: 600px) {
    .shipping-grid {
        grid-template-columns: 1fr;
    }
    .shipping-actions {
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>
