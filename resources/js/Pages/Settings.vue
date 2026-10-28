<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { Save, Building2, Image, Megaphone, Globe, MapPin } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";
import MediaPicker from "../Components/MediaPicker.vue";
const props = defineProps({ settings: Object });
const fields = [
    ["company_name", "Company name", "text"],
    ["phone_one", "Phone one", "tel"],
    ["phone_two", "Phone two", "tel"],
    ["email_one", "Email one", "email"],
    ["email_two", "Email two", "email"],
    ["copyright", "Copyright", "text"],
];
const socials = [
    ["facebook", "Facebook"],
    ["linkedin", "LinkedIn"],
    ["youtube", "YouTube"],
    ["whatsapp", "WhatsApp"],
];
const logos = [
    ["header_logo", "Header logo"],
    ["footer_logo", "Footer logo"],
    ["favicon", "Favicon"],
];
const uploads = logos;
const initial = Object.fromEntries(
    [
        ...fields.map((f) => f[0]),
        "maps",
        "meta_pixel_id",
        "gtm_container_id",
        "address",
        "footer_description",
        "topbar_text",
        "topbar_highlight",
        ...socials.map((f) => f[0]),
    ].map((k) => [k, props.settings[k] || ""]),
);
uploads.forEach(([key]) => {
    initial[key] = null;
    initial[key + "_path"] = "";
    initial["remove_" + key] = false;
});
initial.meta_pixel_enabled = Boolean(props.settings.meta_pixel_enabled);
initial.gtm_enabled = Boolean(props.settings.gtm_enabled);
const form = useForm(initial);
form.topbar_text = props.settings.topbar_text ?? 'Everything You Need,';
form.topbar_highlight = props.settings.topbar_highlight ?? 'One Place';
function save() {
    form.post("/admin/settings", {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            uploads.forEach(([key]) => {
                form[key] = null;
                form[key + "_path"] = "";
                form["remove_" + key] = false;
            });
        },
    });
}
</script>
<template>
    <Head title="Company settings" /><AdminLayout title="Settings"
        ><div class="page-heading">
            <div>
                <span class="eyebrow">MAKE IT YOURS</span>
                <h1>Company settings</h1>
                <p>
                    Your brand and contact details, connected to the storefront.
                </p>
            </div>
            <a href="/" target="_blank" class="btn" rel="noopener"
                >View storefront ↗</a
            >
        </div>
        <form @submit.prevent="save" class="settings-form">
            <section class="panel form-panel">
                <div class="form-panel-title"><Megaphone /><div><h3>Topbar text</h3><p>The message displayed at the top of your website.</p></div></div>
                <div class="form-grid">
                    <Field id="topbar_text" label="Main text" :error="form.errors.topbar_text"><input id="topbar_text" v-model="form.topbar_text" maxlength="150" placeholder="Everything You Need," /></Field>
                    <Field id="topbar_highlight" label="Highlighted text" :error="form.errors.topbar_highlight" hint="This part appears in red."><input id="topbar_highlight" v-model="form.topbar_highlight" maxlength="100" placeholder="One Place" /></Field>
                </div>
            </section>
            <section class="panel form-panel">
                <div class="form-panel-title"><Building2 /><div><h3>Footer description</h3><p>The text displayed below your footer logo.</p></div></div>
                <Field id="footer_description" label="Footer description" :error="form.errors.footer_description"><textarea id="footer_description" v-model="form.footer_description" rows="4" maxlength="1500" placeholder="Tell visitors about your store…" /></Field>
            </section>
            <section class="panel form-panel">
                <div class="form-panel-title"><Globe /><div><h3>Analytics &amp; conversion tracking</h3><p>Connect your Meta Pixel and Google Tag Manager.</p></div></div>
                <div class="form-grid">
                    <Field id="meta_pixel_id" label="Meta Pixel ID" :error="form.errors.meta_pixel_id" hint="Enter the numeric Pixel ID from Meta Events Manager."><input id="meta_pixel_id" v-model="form.meta_pixel_id" inputmode="numeric" maxlength="25" placeholder="Pixel ID" /></Field>
                    <Field id="gtm_container_id" label="Google Tag Manager Container ID" :error="form.errors.gtm_container_id" hint="Your web container ID begins with GTM-."><input id="gtm_container_id" v-model="form.gtm_container_id" maxlength="24" placeholder="GTM-XXXXXXX" /></Field>
                </div>
                <div style="display:flex;gap:24px;flex-wrap:wrap;margin-top:18px"><label style="display:flex;gap:8px;align-items:center"><input type="checkbox" v-model="form.meta_pixel_enabled" /> Enable Meta Pixel</label><label style="display:flex;gap:8px;align-items:center"><input type="checkbox" v-model="form.gtm_enabled" /> Enable Google Tag Manager</label></div>
                <p style="font-size:12px;color:#718078;margin-top:14px">Events use BDT and product IDs. Purchase tracks placed cash-on-delivery orders and verified online payments. Configure Google Analytics tags in your GTM container; keep Meta Pixel tags there disabled when using the direct Pixel above.</p>
            </section>
            <section class="panel form-panel">
                <div class="form-panel-title">
                    <Building2 />
                    <div>
                        <h3>Company details</h3>
                        <p>Keep your customers connected.</p>
                    </div>
                </div>
                <div class="form-grid">
                    <Field
                        v-for="[key, label, type] in fields"
                        :key="key"
                        :id="key"
                        :label="label"
                        :error="form.errors[key]"
                        ><input
                            :id="key"
                            v-model="form[key]"
                            :type="type"
                            :required="key === 'company_name'"
                            :maxlength="
                                key === 'copyright' ? 300 : 255
                            " /></Field
                    ><Field
                        id="address"
                        label="Address"
                        :error="form.errors.address"
                    >
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            maxlength="1000"
                        ></textarea>
                    </Field>
                </div>
            </section>
            <section class="panel form-panel">
                <div class="form-panel-title">
                    <MapPin />
                    <div>
                        <h3>Google Maps link</h3>
                        <p>Add a shareable map URL for your store location.</p>
                    </div>
                </div>
                <Field
                    id="maps"
                    label="Google Maps URL"
                    :error="form.errors.maps"
                    hint="Paste a Google Maps link, not iframe embed code."
                >
                    <input
                        id="maps"
                        v-model="form.maps"
                        type="url"
                        maxlength="2048"
                        placeholder="https://maps.google.com/..."
                    />
                </Field>
            </section>
            <section class="panel form-panel">
                <div class="form-panel-title">
                    <Image />
                    <div>
                        <h3>Brand assets</h3>
                        <p>
                            Images are optimized to WebP. Upload new files from
                            Media; use a square image for the favicon.
                        </p>
                    </div>
                </div>
                <div class="logo-grid">
                    <div
                        v-for="[key, label] in logos"
                        :key="key"
                        class="logo-field"
                    >
                        <div class="logo-preview">
                            <img
                                v-if="
                                    !form['remove_' + key] &&
                                    (form[key + '_path'] || settings[key])
                                "
                                :src="form[key + '_path'] || settings[key]"
                                :alt="label"
                            /><span v-else>No image</span>
                        </div>
                        <Field
                            :id="key"
                            :label="label"
                            :error="
                                form.errors[key] || form.errors[key + '_path']
                            "
                            hint="Choose from the media library. Upload new files from Media."
                        >
                            <MediaPicker
                                :id="key"
                                v-model="form[key + '_path']"
                                file-input
                                :label="'Choose ' + label.toLowerCase()"
                                @update:model-value="
                                    form[key] = null;
                                    form['remove_' + key] = false;
                                "
                            />
                        </Field>
                        <label
                            v-if="settings[key] && !form[key + '_path']"
                            class="remove-logo"
                            ><input
                                v-model="form['remove_' + key]"
                                type="checkbox"
                            />
                            Remove current image</label
                        >
                    </div>
                </div>
            </section>


            <section class="panel form-panel">
                <div class="form-panel-title">
                    <Globe />
                    <div>
                        <h3>Social media</h3>
                        <p>Only links you fill in will appear in the footer.</p>
                    </div>
                </div>
                <div class="form-grid">
                    <Field
                        v-for="[key, label] in socials"
                        :key="key"
                        :id="key"
                        :label="label"
                        :error="form.errors[key]"
                        ><input
                            :id="key"
                            v-model="form[key]"
                            type="url"
                            maxlength="2048"
                            placeholder="https://"
                    /></Field>
                </div>
            </section>
            <div class="settings-save">
                <button class="btn primary" :disabled="form.processing">
                    <Save :size="18" />{{
                        form.processing ? "Saving…" : "Save settings"
                    }}
                </button>
            </div>
        </form></AdminLayout
    >
</template>
<style scoped>
.settings-form {
    max-width: 1100px;
    display: grid;
    gap: 24px;
}
.logo-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 24px;
}
.logo-preview {
    height: 130px;
    border: 1px dashed #cddbd5;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f7f6;
    margin-bottom: 16px;
    color: #718079;
}
.logo-preview img {
    max-width: 90%;
    max-height: 105px;
    object-fit: contain;
}
.logo-field {
    min-width: 0;
}
.remove-logo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}
.remove-logo input {
    width: auto;
}
.settings-save {
    position: sticky;
    bottom: 16px;
    padding: 16px;
    border: 1px solid #e0e8e3;
    background: white;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 16px;
    box-shadow: 0 8px 30px #1233200a;
}
textarea {
    width: 100%;
    resize: vertical;
}
@media (max-width: 760px) {
    .logo-grid {
        grid-template-columns: 1fr;
    }
}
</style>
