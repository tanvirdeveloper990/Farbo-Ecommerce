<script setup>
import { computed } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { UserRound, ShieldCheck, Check } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import CustomerLayout from "../Components/CustomerLayout.vue";
import Field from "../Components/Field.vue";
import PasswordInput from "../Components/PasswordInput.vue";
defineProps({ admin: Boolean });
const page = usePage();
const user = computed(() => page.props.auth.user);
const completeness = computed(() => Math.round([user.value.name, user.value.email, user.value.phone, user.value.address].filter(Boolean).length / 4 * 100));
const profile = useForm({
    name: user.value.name,
    email: user.value.email,
    phone: user.value.phone || "",
    address: user.value.address || "",
});
const password = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});
function savePassword() {
    password.put("/password", {
        preserveScroll: true,
        onSuccess: () => password.reset(),
    });
}
</script>
<template>
    <Head title="My profile" /><component
        :is="admin ? AdminLayout : CustomerLayout"
        title="My profile"
        >
        <section v-if="!admin" class="profile-welcome">
            <div><span class="profile-welcome-kicker">YOUR PERSONAL SPACE</span><h1>Hello, {{ user.name.split(' ')[0] }}.</h1><p>Manage your details and keep your next checkout effortless.</p></div>
            <div class="profile-completion"><UserRound :size="23" /><strong>{{ completeness }}%</strong><span>Profile complete</span><div class="profile-completion-track"><i :style="{ width: completeness + '%' }"></i></div></div>
        </section>
        <div v-if="admin" class="page-heading">
            <div>
                <span class="eyebrow">A SPACE THAT’S YOURS</span>
                <h1>My profile</h1>
                <p>Your details, preferences, and peace of mind.</p>
            </div>
        </div>
        <div class="profile-grid" :class="{ 'customer-profile-grid': !admin }">
            <aside v-if="admin" class="panel profile-summary">
                <span class="avatar large">{{
                    user.name.slice(0, 2).toUpperCase()
                }}</span>
                <h3>{{ user.name }}</h3>
                <p>{{ user.email }}</p>
                <span class="pill">{{
                    user.is_super_admin
                        ? "Store owner"
                        : admin
                          ? "Administrator"
                          : "Nest member"
                }}</span>
                <div class="profile-summary-note">
                    <ShieldCheck :size="20" />
                    <p>
                        Your details stay private<br />
                        and safe with us.
                    </p>
                </div>
            </aside>
            <div>
                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <UserRound :size="21" />
                        <div>
                            <h3>Personal information</h3>
                            <p>A few details to help us know you better.</p>
                        </div>
                    </div>
                    <form
                        @submit.prevent="
                            profile.patch('/profile', { preserveScroll: true })
                        "
                        novalidate
                    >
                        <div class="form-grid">
                            <Field
                                label="Full name"
                                id="profile-name"
                                :error="profile.errors.name"
                                ><input
                                    id="profile-name"
                                    v-model="profile.name"
                                    autocomplete="name"
                                    :aria-invalid="
                                        !!profile.errors.name
                                    " /></Field
                            ><Field
                                label="Email address"
                                id="profile-email"
                                :error="profile.errors.email"
                                ><input
                                    id="profile-email"
                                    v-model="profile.email"
                                    type="email"
                                    autocomplete="email"
                                    :aria-invalid="!!profile.errors.email"
                            /></Field>
                        </div>
                        <Field
                            label="Phone number (optional)"
                            id="phone"
                            :error="profile.errors.phone"
                            ><input
                                id="phone"
                                v-model="profile.phone"
                                type="tel"
                                autocomplete="tel"
                                placeholder="Your phone number" /></Field
                        ><Field
                            label="Address (optional)"
                            id="address"
                            :error="profile.errors.address"
                        >
                            <textarea
                                id="address"
                                v-model="profile.address"
                                rows="3"
                                autocomplete="street-address"
                                placeholder="Your address"
                            />
                        </Field>
                        <div class="form-footer">
                            <small role="status">{{ profile.recentlySuccessful ? 'Your profile has been updated.' : 'Keep your details up to date.' }}</small
                            ><button
                                class="btn primary"
                                :disabled="profile.processing"
                            >
                                {{
                                    profile.processing
                                        ? "Saving…"
                                        : "Save changes"
                                }}<Check :size="16" />
                            </button>
                        </div>
                    </form>
                </section>
                <section v-if="admin" class="panel form-panel" id="password">
                    <div class="form-panel-title">
                        <ShieldCheck :size="21" />
                        <div>
                            <h3>Change password</h3>
                            <p>A little security goes a long way.</p>
                        </div>
                    </div>
                    <form @submit.prevent="savePassword" novalidate>
                        <Field
                            label="Current password"
                            id="current-password"
                            :error="password.errors.current_password"
                            ><PasswordInput
                                id="current-password"
                                v-model="password.current_password"
                                autocomplete="current-password"
                                :aria-invalid="
                                    !!password.errors.current_password
                                "
                        /></Field>
                        <div class="form-grid">
                            <Field
                                label="New password"
                                id="new-password"
                                :error="password.errors.password"
                                hint="8+ characters, uppercase, lowercase and a number."
                                ><PasswordInput
                                    id="new-password"
                                    v-model="password.password"
                                    autocomplete="new-password"
                                    :aria-invalid="
                                        !!password.errors.password
                                    " /></Field
                            ><Field
                                label="Confirm new password"
                                id="confirm-password"
                                :error="password.errors.password_confirmation"
                                ><PasswordInput
                                    id="confirm-password"
                                    v-model="password.password_confirmation"
                                    autocomplete="new-password"
                            /></Field>
                        </div>
                        <div class="form-footer">
                            <small
                                >Choose a password you haven’t used
                                before.</small
                            ><button
                                class="btn primary"
                                :disabled="password.processing"
                            >
                                {{
                                    password.processing
                                        ? "Updating…"
                                        : "Update password"
                                }}
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div></component
    >
</template>
<style scoped>
.customer-profile-grid {
    display: block;
}
.customer-profile-grid > div {
    display: grid;
    gap: 18px;
}
.profile-welcome{display:flex;align-items:center;justify-content:space-between;gap:24px;margin-bottom:25px;padding:32px;border:1px solid #DCE4EF;border-radius:20px;background:linear-gradient(120deg,#F3F6FB,#F3F6FB);color:#0B2A5B}.profile-welcome-kicker{font-size:9px;letter-spacing:.2em;font-weight:700;color:#123A75}.profile-welcome h1{font-size:32px;line-height:1.2;letter-spacing:-.8px;font-weight:650;margin:9px 0 12px}.profile-welcome p{font-size:13px;color:#64748B;max-width:370px;line-height:1.7}.profile-completion{display:grid;grid-template-columns:auto auto;align-items:center;gap:8px 12px;min-width:130px;color:#E30613}.profile-completion strong{font-size:27px}.profile-completion>span,.profile-completion-track{grid-column:1/-1}.profile-completion>span{font-size:11px;color:#64748B}.profile-completion-track{height:4px;background:#DCE4EF;border-radius:4px;overflow:hidden}.profile-completion-track i{display:block;height:100%;background:#E30613;border-radius:4px;transition:width .3s}.customer-profile-grid .panel{border:1px solid #DCE4EF;border-radius:18px;background:white;overflow:hidden;box-shadow:0 10px 30px #35221b05}.customer-profile-grid .form-panel{padding:28px}.customer-profile-grid .form-panel-title{display:flex;align-items:center;gap:13px;margin-bottom:25px}.customer-profile-grid .form-panel-title>svg{width:42px;height:42px;padding:11px;border-radius:12px;background:#F3F6FB;color:#E30613}.customer-profile-grid h3{font-size:17px;font-weight:650;color:#0B2A5B}.customer-profile-grid .form-panel-title p{font-size:12px;color:#64748B;margin-top:5px}.customer-profile-grid form{display:grid;gap:18px}.customer-profile-grid .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.customer-profile-grid .form-footer{display:flex;justify-content:space-between;align-items:center;gap:15px;border-top:1px solid #DCE4EF;padding-top:20px;margin-top:6px}.customer-profile-grid .form-footer small{color:#64748B;font-size:11px}.customer-profile-grid .btn{display:flex;align-items:center;gap:10px;border:0;border-radius:9px;background:#E30613;color:white;padding:12px 18px;font-size:12px;font-weight:600}.customer-profile-grid .btn:hover{background:#C10510}.customer-profile-grid .btn:disabled{opacity:.6}@media(max-width:600px){.profile-welcome{padding:23px;gap:20px;align-items:flex-start}.profile-welcome h1{font-size:26px}.profile-welcome p{font-size:12px}.profile-completion{min-width:85px}.profile-completion>svg{display:none}.profile-completion strong{grid-column:1/-1;font-size:24px}.customer-profile-grid .form-panel{padding:22px}.customer-profile-grid .form-grid{grid-template-columns:1fr}.customer-profile-grid .form-footer{align-items:flex-start;flex-direction:column}}
</style>
