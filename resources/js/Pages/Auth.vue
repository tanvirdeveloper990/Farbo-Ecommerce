<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    Sprout,
    ArrowRight,
    EyeOff,
    ShieldCheck,
    ArrowLeft,
    Check,
} from "@lucide/vue";
import Field from "../Components/Field.vue";
import PasswordInput from "../Components/PasswordInput.vue";
import StorefrontLayout from "../Storefront/StorefrontLayout.vue";
import "../../css/storefront.css";
const props = defineProps({ mode: String, admin: Boolean });
const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    remember: false,
});
const passwordChecks = computed(() => ({
    length: form.password.length >= 8,
    uppercase: /[A-Z]/.test(form.password),
    lowercase: /[a-z]/.test(form.password),
    number: /\d/.test(form.password),
}));
const passwordIsValid = computed(() =>
    Object.values(passwordChecks.value).every(Boolean),
);
function submit() {
    if (props.mode === "register") {
        form.clearErrors("password", "password_confirmation");
        if (!passwordIsValid.value) {
            form.setError(
                "password",
                "Password must be at least 8 characters and include uppercase, lowercase, and a number.",
            );
            return;
        }
        if (form.password !== form.password_confirmation) {
            form.setError(
                "password_confirmation",
                "The password confirmation does not match.",
            );
            return;
        }
    }
    form.post(
        props.mode === "register"
            ? "/register"
            : props.admin
              ? "/admin/login"
              : "/login",
        {
            onSuccess: () => form.reset("password", "password_confirmation"),
        },
    );
}
</script>
<template>
    <Head
        :title="
            mode === 'register'
                ? 'Create account'
                : admin
                  ? 'Admin login'
                  : 'Welcome back'
        "
    />
    <component
        :is="admin ? 'div' : StorefrontLayout"
        :class="admin ? 'auth-page' : 'customer-auth-page'"
    >
        <section v-if="admin" class="auth-story">
            <Link href="/" class="brand light"
                ><span class="brand-mark"><Sprout /></span>FARBO</Link
            >
            <div class="story-copy">
                <span class="eyebrow light">{{
                    admin
                        ? "YOUR STORE. YOUR POSSIBILITIES."
                        : "GOOD THINGS START HERE."
                }}</span>
                <h1>
                    {{
                        admin
                            ? "A little clarity.\nA lot of potential."
                            : "Make yourself\nat home."
                    }}
                </h1>
                <p>
                    {{
                        admin
                            ? "A thoughtful space to manage your store, support your customers, and build what comes next."
                            : "Your favorites, your account, your little corner of FARBO. Everything you need, beautifully simple."
                    }}
                </p>
                <div class="story-art">
                    <div class="orbit orbit-one"></div>
                    <div class="orbit orbit-two"></div>
                    <div class="art-card">
                        <span class="art-icon"><Sprout :size="32" /></span
                        ><span>Room to grow.</span
                        ><small>Built around you.</small>
                        <div class="mini-bars">
                            <i
                                v-for="n in 7"
                                :key="n"
                                :style="{ height: 20 + n * 10 + 'px' }"
                            ></i>
                        </div>
                    </div>
                    <span class="floating-pill"
                        ><Check :size="16" /> A fresh perspective</span
                    >
                </div>
            </div>
            <small class="story-footer"
                >Considered commerce. Everyday possibility.</small
            >
        </section>
        <section
            class="auth-form-side"
            :class="{ 'customer-auth-side': !admin }"
        >
            <Link href="/" class="back-link"
                ><ArrowLeft :size="16" /> Back to store</Link
            >
            <div class="auth-form-wrap">
                <span class="auth-badge"
                    ><ShieldCheck :size="16" />
                    {{ admin ? "ADMIN WORKSPACE" : "YOUR FARBO ACCOUNT" }}</span
                >
                <h2>
                    {{
                        mode === "register"
                            ? "Your next chapter."
                            : "Welcome back."
                    }}
                </h2>
                <p>
                    {{
                        mode === "register"
                            ? "Create an account and settle right in."
                            : admin
                              ? "Sign in to your workspace. Let’s make things happen."
                              : "Good to see you again. Sign in to your account."
                    }}
                </p>
                <form @submit.prevent="submit" novalidate>
                    <Field
                        v-if="mode === 'register'"
                        label="Full name"
                        id="name"
                        :error="form.errors.name"
                        ><input
                            id="name"
                            v-model="form.name"
                            autocomplete="name"
                            placeholder="Your full name"
                            :aria-invalid="!!form.errors.name" /></Field
                    ><Field
                        label="Email address"
                        id="email"
                        :error="form.errors.email"
                        ><input
                            id="email"
                            type="email"
                            v-model="form.email"
                            autocomplete="email"
                            placeholder="you@example.com"
                            :aria-invalid="!!form.errors.email"
                    /></Field>
                    <Field
                        label="Password"
                        id="password"
                        :error="form.errors.password"
                        ><PasswordInput
                            id="password"
                            v-model="form.password"
                            :autocomplete="
                                mode === 'register'
                                    ? 'new-password'
                                    : 'current-password'
                            "
                            placeholder="Enter your password"
                            :aria-invalid="!!form.errors.password"
                    /></Field>
                    <ul
                        v-if="mode === 'register'"
                        class="password-requirements"
                        aria-label="Password requirements"
                    >
                        <li :class="{ met: passwordChecks.length }">
                            At least 8 characters
                        </li>
                        <li :class="{ met: passwordChecks.uppercase }">
                            One uppercase letter
                        </li>
                        <li :class="{ met: passwordChecks.lowercase }">
                            One lowercase letter
                        </li>
                        <li :class="{ met: passwordChecks.number }">
                            One number
                        </li>
                    </ul>
                    <Field
                        v-if="mode === 'register'"
                        label="Confirm password"
                        id="confirmation"
                        :error="form.errors.password_confirmation"
                        ><PasswordInput
                            id="confirmation"
                            v-model="form.password_confirmation"
                            autocomplete="new-password"
                            placeholder="Enter your password again" /></Field
                    ><label v-else class="check-label"
                        ><input type="checkbox" v-model="form.remember" /> Keep
                        me signed in</label
                    ><button
                        class="btn primary auth-submit"
                        :disabled="form.processing"
                    >
                        {{
                            form.processing
                                ? "Please wait…"
                                : mode === "register"
                                  ? "Create account"
                                  : admin
                                    ? "Sign in to workspace"
                                    : "Sign in"
                        }}<ArrowRight :size="18" />
                    </button>
                </form>
                <p v-if="!admin" class="auth-switch">
                    {{
                        mode === "register"
                            ? "Already part of FARBO?"
                            : "New around here?"
                    }}
                    <Link
                        :href="mode === 'register' ? '/login' : '/register'"
                        >{{
                            mode === "register"
                                ? "Sign in"
                                : "Create an account"
                        }}</Link
                    >
                </p>
            </div>
            <small class="auth-copyright"
                >© {{ new Date().getFullYear() }} FARBO. All rights
                reserved.</small
            >
        </section>
    </component>
</template>
<style scoped>
.customer-auth-page :deep(main) {
    display: flex;
    min-height: min(760px, 78vh);
    align-items: center;
    justify-content: center;
    padding: 38px 20px;
    background: #F3F6FB;
}
.customer-auth-side {
    width: min(100%, 510px);
    min-height: 0;
    border: 1px solid #dce4ef;
    border-radius: 16px;
    background: #fff;
    padding: 26px clamp(22px, 5vw, 52px);
    box-shadow: 0 18px 55px #0B2A5B0d;
}
.customer-auth-side .auth-form-wrap {
    max-width: 420px;
    margin: auto;
    padding: 22px 0;
}
.customer-auth-side .back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: 11px;
}
.customer-auth-side .auth-badge {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-bottom: 17px;
    color: #64748b;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.13em;
}
.customer-auth-side .auth-form-wrap h2 {
    margin-bottom: 8px;
    color: #0B2A5B;
    font-size: 30px;
    font-weight: 650;
    letter-spacing: -0.8px;
}
.customer-auth-side .auth-form-wrap > p {
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
}
.customer-auth-side .auth-form-wrap form {
    margin-top: 23px;
}
.customer-auth-side :deep(.field) {
    display: grid;
    gap: 7px;
    margin-bottom: 17px;
}
.customer-auth-side :deep(.field > label) {
    color: #0B2A5B;
    font-size: 11px;
    font-weight: 600;
}
.customer-auth-side :deep(.field input:not([type="checkbox"])) {
    width: 100%;
    min-height: 44px;
    border: 1px solid #dce4ef;
    border-radius: 8px;
    background: #fff;
    padding: 11px 13px;
    color: #0B2A5B;
    font-size: 12px;
    outline: none;
}
.customer-auth-side :deep(.field input:focus) {
    border-color: #123A75;
    box-shadow: 0 0 0 3px #E3061312;
}
.customer-auth-side :deep(.field-error) {
    color: #b42318;
    font-size: 10px;
}
.customer-auth-side :deep(.field-hint) {
    color: #64748b;
    font-size: 10px;
}
.customer-auth-side .password-requirements {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 5px 12px;
    margin: -8px 0 16px;
    padding: 0;
    color: #64748B;
    font-size: 10px;
    list-style: none;
}
.customer-auth-side .password-requirements li::before {
    content: "○";
    margin-right: 6px;
}
.customer-auth-side .password-requirements li.met {
    color: #28734f;
}
.customer-auth-side .password-requirements li.met::before {
    content: "✓";
}
.customer-auth-side :deep(.password-input) {
    position: relative;
}
.customer-auth-side :deep(.password-input input) {
    padding-right: 43px !important;
}
.customer-auth-side :deep(.password-input button) {
    position: absolute;
    top: 7px;
    right: 8px;
    display: grid;
    width: 30px;
    height: 30px;
    place-items: center;
    color: #64748b;
}
.customer-auth-side .check-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0 0 17px;
    color: #64748b;
    font-size: 11px;
}
.customer-auth-side .auth-submit {
    display: flex;
    width: 100%;
    min-height: 45px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border: 0;
    border-radius: 8px;
    background: #E30613;
    padding: 0 15px;
    color: white;
    font-size: 12px;
    font-weight: 650;
}
.customer-auth-side .auth-submit:hover {
    background: #C10510;
}
.customer-auth-side .auth-submit:disabled {
    cursor: wait;
    opacity: 0.65;
}
.customer-auth-side .auth-switch {
    margin-top: 17px;
    color: #64748b;
    font-size: 11px;
    text-align: center;
}
.customer-auth-side .auth-switch a {
    color: #E30613;
    font-weight: 700;
}
.customer-auth-side .auth-copyright {
    padding-top: 14px;
    color: #64748B;
    font-size: 9px;
    text-align: center;
}
@media (max-width: 640px) {
    .customer-auth-page :deep(main) {
        min-height: 65vh;
        padding: 25px 15px;
    }
    .customer-auth-side {
        padding: 22px 18px;
    }
    .customer-auth-side .password-requirements {
        grid-template-columns: 1fr;
    }
}
</style>
