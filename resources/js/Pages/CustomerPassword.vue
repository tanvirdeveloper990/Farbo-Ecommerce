<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { KeyRound, Save } from "@lucide/vue";
import CustomerLayout from "../Components/CustomerLayout.vue";
import Field from "../Components/Field.vue";
import PasswordInput from "../Components/PasswordInput.vue";

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});
function savePassword() {
    form.put("/password", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head title="Change password" />
    <CustomerLayout>
        <div class="account-page-heading">
            <span class="account-eyebrow">ACCOUNT SECURITY</span>
            <h1>Change password</h1>
            <p>Choose a strong password to keep your account protected.</p>
        </div>
        <section class="account-panel password-panel">
            <div class="account-panel-heading">
                <span class="account-panel-icon"><KeyRound :size="19" /></span>
                <div>
                    <h2>Update your password</h2>
                    <p>
                        Use at least 8 characters with uppercase, lowercase and
                        a number.
                    </p>
                </div>
            </div>
            <form class="password-form" @submit.prevent="savePassword">
                <Field
                    label="Current password"
                    id="customer-current-password"
                    :error="form.errors.current_password"
                >
                    <PasswordInput
                        id="customer-current-password"
                        v-model="form.current_password"
                        autocomplete="current-password"
                    />
                </Field>
                <Field
                    label="New password"
                    id="customer-new-password"
                    :error="form.errors.password"
                >
                    <PasswordInput
                        id="customer-new-password"
                        v-model="form.password"
                        autocomplete="new-password"
                    />
                </Field>
                <Field
                    label="Confirm new password"
                    id="customer-confirm-password"
                    :error="form.errors.password_confirmation"
                >
                    <PasswordInput
                        id="customer-confirm-password"
                        v-model="form.password_confirmation"
                        autocomplete="new-password"
                    />
                </Field>
                <button
                    class="account-primary-button"
                    :disabled="form.processing"
                >
                    <Save :size="16" />
                    {{ form.processing ? "Updating..." : "Update password" }}
                </button>
            </form>
        </section>
    </CustomerLayout>
</template>

<style scoped>
.account-page-heading {
    margin: 2px 0 24px;
}
.account-eyebrow {
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.17em;
}
.account-page-heading h1 {
    margin-top: 7px;
    color: #0B2A5B;
    font-size: clamp(24px, 3vw, 32px);
}
.account-page-heading p {
    margin-top: 5px;
}
.account-panel {
    border: 1px solid #dce4ef;
    border-radius: 13px;
    background: white;
    padding: clamp(18px, 3vw, 28px);
    box-shadow: 0 8px 30px #0B2A5B08;
}
.account-panel-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
    border-bottom: 1px solid #dce4ef;
    padding-bottom: 17px;
}
.account-panel-icon {
    display: grid;
    width: 40px;
    height: 40px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 11px;
    background: #F3F6FB;
    color: #E30613;
}
.account-panel-heading h2 {
    color: #0B2A5B;
    font-size: 15px;
}
.account-panel-heading p {
    margin-top: 3px;
    font-size: 11px;
}
.password-form {
    display: grid;
    gap: 17px;
    max-width: 620px;
}
.account-primary-button {
    display: inline-flex;
    min-height: 44px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 8px;
    background: #E30613;
    padding: 0 18px;
    color: white;
    font-size: 12px;
    font-weight: 650;
}
.account-primary-button:hover {
    background: #C10510;
}
@media (max-width: 480px) {
    .account-primary-button {
        width: 100%;
    }
}
</style>
