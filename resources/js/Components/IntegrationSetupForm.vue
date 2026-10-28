<script setup>
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Save, LockKeyhole } from "@lucide/vue";
import Field from "./Field.vue";
import PasswordInput from "./PasswordInput.vue";
const props = defineProps({ setup: Object, endpoint: String, number: String });
const initial = () =>
    Object.fromEntries([
        ...props.setup.fields.map((f) => [
            f.key,
            f.type === "password" ? "" : props.setup.values[f.key] || "",
        ]),
        ...(props.setup.provider === "sslcommerz"
            ? [
                  ["mode", props.setup.values.mode],
                  ["status", props.setup.values.status],
              ]
            : []),
    ]);
const form = useForm(initial());
const secretFields = computed(() =>
    props.setup.fields.filter((f) => f.type === "password").map((f) => f.key),
);
function save() {
    form.put(props.endpoint, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset(...secretFields.value);
        },
    });
}
watch(
    () => props.setup.provider,
    () => {
        form.defaults(initial());
        form.reset();
        form.clearErrors();
    },
);
</script>
<template>
    <section class="panel integration-panel">
        <header class="integration-heading">
            <div class="provider-mark">{{ number }}</div>
            <div>
                <h2>{{ setup.title }}</h2>
                <p>
                    {{
                        setup.provider === "sslcommerz"
                            ? "Your payment gateway configuration."
                            : setup.provider === "smtp"
                              ? "Manage your outgoing email connection details."
                              : "Manage this courier’s connection details."
                    }}
                </p>
            </div>
        </header>
        <form @submit.prevent="save">
            <div class="integration-fields">
                <Field
                    v-for="field in setup.fields"
                    :key="field.key"
                    :id="setup.provider + '-' + field.key"
                    :label="field.label + (field.required ? ' *' : '')"
                    :error="form.errors[field.key]"
                    :hint="
                        field.type === 'password' && setup.saved[field.key]
                            ? 'Saved securely. Leave blank to keep the existing value.'
                            : undefined
                    "
                    ><PasswordInput
                        v-if="field.type === 'password'"
                        :id="setup.provider + '-' + field.key"
                        v-model="form[field.key]"
                        :required="field.required && !setup.saved[field.key]"
                        autocomplete="new-password"
                        :placeholder="
                            setup.saved[field.key]
                                ? 'Saved — enter only to replace'
                                : 'Enter ' + field.label
                        "
                        maxlength="1000"
                        :aria-invalid="!!form.errors[field.key]" /><select
                        v-else-if="field.type === 'select'"
                        :id="setup.provider + '-' + field.key"
                        v-model="form[field.key]"
                        :required="field.required"
                        :aria-invalid="!!form.errors[field.key]"
                    >
                        <option
                            v-for="(label, value) in field.options"
                            :key="value"
                            :value="value"
                        >
                            {{ label }}
                        </option></select
                    ><input
                        v-else
                        :id="setup.provider + '-' + field.key"
                        v-model="form[field.key]"
                        :type="field.type"
                        :min="field.type === 'number' ? 1 : undefined"
                        :max="field.type === 'number' ? 65535 : undefined"
                        :step="field.type === 'number' ? 1 : undefined"
                        :required="field.required"
                        :maxlength="field.type === 'url' ? 2048 : 1000"
                        :placeholder="
                            field.type === 'url' ? 'https://' : field.label
                        "
                        :aria-invalid="!!form.errors[field.key]" /></Field
                ><template v-if="setup.provider === 'sslcommerz'"
                    ><Field
                        id="sslcommerz-mode"
                        label="SSLcz Sandbox *"
                        :error="form.errors.mode"
                        ><select
                            id="sslcommerz-mode"
                            v-model="form.mode"
                            required
                        >
                            <option value="test">Test</option>
                            <option value="live">Live</option>
                        </select></Field
                    ><Field
                        id="sslcommerz-status"
                        label="Status"
                        :error="form.errors.status"
                        ><select id="sslcommerz-status" v-model="form.status">
                            <option :value="false">Inactive</option>
                            <option :value="true">Active</option>
                        </select></Field
                    ></template
                >
            </div>
            <footer class="integration-footer">
                <div class="secure-note">
                    <LockKeyhole :size="15" /><span
                        >Keys and passwords are stored securely.</span
                    >
                </div>
                <button class="btn primary" :disabled="form.processing">
                    <Save :size="17" />{{
                        form.processing
                            ? "Updating…"
                            : "Update " +
                              (setup.provider === "sslcommerz"
                                  ? "SSLcommerz"
                                  : setup.provider === "steadfast"
                                    ? "Steadfast"
                                    : setup.provider === "pathao"
                                      ? "Pathao"
                                      : setup.provider === "smtp"
                                        ? "SMTP"
                                        : "REDX")
                    }}
                </button>
            </footer>
        </form>
    </section>
</template>
<style scoped>
.integration-panel {
    padding: 30px;
    margin-bottom: 26px;
}
.integration-heading {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 28px;
    padding-bottom: 24px;
    border-bottom: 1px solid #edf1e9;
}
.provider-mark {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    background: #eaf1e4;
    color: #487342;
    display: grid;
    place-items: center;
    font-size: 18px;
    font-weight: 600;
    flex-shrink: 0;
}
.integration-heading h2 {
    font-size: 19px;
    margin: 0 0 7px;
}
.integration-heading p {
    font-size: 12px;
    color: #849080;
    margin: 0;
}
.integration-fields {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px 24px;
}
.integration-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    border-top: 1px solid #edf1e9;
    padding-top: 23px;
    margin-top: 10px;
}
.secure-note {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #85917f;
    font-size: 11px;
}
.secure-note svg {
    flex-shrink: 0;
}
@media (max-width: 700px) {
    .integration-panel {
        padding: 22px 18px;
    }
    .integration-fields {
        grid-template-columns: 1fr;
    }
    .integration-footer {
        align-items: stretch;
        flex-direction: column;
    }
    .integration-heading {
        gap: 12px;
    }
    .integration-heading h2 {
        font-size: 17px;
    }
}
</style>
