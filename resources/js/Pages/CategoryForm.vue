<script setup>
import { computed, watch } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ChevronLeft, Eye, ImagePlus, House, PanelLeft, PanelsTopLeft, Save, Star } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";
import MediaPicker from "../Components/MediaPicker.vue";

const props = defineProps({
    record: Object,
    type: String,
    categories: Array,
    subcategories: Array,
});

const form = useForm({
    type: props.type || "category",
    parent_id: props.record?.parent_id || "",
    name: props.record?.name || "",
    image_path: "",
    remove_image: false,
    is_active: props.record?.is_active ?? true,
    show_in_header: props.record?.show_in_header ?? true,
    show_on_home: props.record?.show_on_home ?? true,
    show_on_sidebar: props.record?.show_on_sidebar ?? true,
    is_popular: props.record?.is_popular ?? false,
});
const visibilityOptions = [
    { key: "show_in_header", label: "Show on header", description: "Display this category in the top navigation menu.", icon: PanelsTopLeft },
    { key: "show_on_home", label: "Show on home page", description: "Feature this category on your store’s home page.", icon: House },
    { key: "show_on_sidebar", label: "Show on sidebar", description: "Include this category in the sidebar menu.", icon: PanelLeft },
    { key: "is_popular", label: "Show in Popular Category", description: "Feature this category in the Popular Category section.", icon: Star },
];
const slug = computed(
    () =>
        form.name
            .trim()
            .toLowerCase()
            .normalize("NFKD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^\p{L}\p{N}]+/gu, "-")
            .replace(/^-|-$/g, "") || "category",
);
const title = computed(() =>
    form.type === "child"
        ? "Child category"
        : form.type === "subcategory"
          ? "Subcategory"
          : "Category",
);
const parentOptions = computed(() =>
    form.type === "child"
        ? props.subcategories
        : form.type === "subcategory"
          ? props.categories
          : [],
);
const imageUrl = computed(() =>
    form.remove_image ? "" : form.image_path || props.record?.image || "",
);

watch(
    () => form.type,
    (type, previousType) => {
        if (type !== previousType) form.parent_id = "";
    },
);

function save() {
    const endpoint = props.record
        ? `/admin/categories/records/${props.record.type}/${props.record.id}`
        : "/admin/categories/records";
    form.post(endpoint, {
        preserveScroll: true,
        onSuccess: () => {
            form.image_path = "";
            form.remove_image = false;
        },
    });
}

function resetImage() {
    form.image_path = "";
    form.remove_image = Boolean(props.record?.image);
}
</script>

<template>
    <Head :title="record ? `Edit ${title}` : `Add ${title}`" />
    <AdminLayout title="Categories">
        <Link href="/admin/categories" class="back-link">
            <ChevronLeft :size="16" />All categories
        </Link>
        <div class="page-heading">
            <div>
                <span class="eyebrow">PRODUCT CATALOG</span>
                <h1>{{ record ? `Edit ${title}` : `Add ${title}` }}</h1>
                <p>Set a parent, name, image and active status.</p>
            </div>
        </div>

        <form class="category-form" @submit.prevent="save">
            <section class="panel form-panel">
                <div class="form-grid">
                    <Field
                        id="category-type"
                        label="Category level"
                        :error="form.errors.type"
                    >
                        <select
                            id="category-type"
                            v-model="form.type"
                            :disabled="Boolean(record)"
                        >
                            <option value="category">Category</option>
                            <option value="subcategory">Subcategory</option>
                            <option value="child">Child category</option>
                        </select>
                    </Field>

                    <Field
                        v-if="form.type !== 'category'"
                        id="category-parent"
                        :label="
                            form.type === 'child'
                                ? 'Parent subcategory'
                                : 'Parent category'
                        "
                        :error="form.errors.parent_id"
                    >
                        <select
                            id="category-parent"
                            v-model="form.parent_id"
                            required
                        >
                            <option value="" disabled>Select parent</option>
                            <option
                                v-for="parent in parentOptions"
                                :key="parent.id"
                                :value="parent.id"
                            >
                                {{
                                    form.type === "child"
                                        ? `${parent.category_name} / ${parent.name}`
                                        : parent.name
                                }}
                            </option>
                        </select>
                    </Field>

                    <Field
                        id="category-name"
                        label="Name"
                        :error="form.errors.name"
                    >
                        <input
                            id="category-name"
                            v-model="form.name"
                            required
                            maxlength="150"
                            placeholder="Enter category name"
                        />
                    </Field>

                    <Field
                        id="category-slug"
                        label="Slug"
                        hint="Generated automatically from the name."
                    >
                        <input
                            id="category-slug"
                            :value="
                                record?.name === form.name ? record.slug : slug
                            "
                            readonly
                        />
                    </Field>

                    <Field
                        id="category-status"
                        label="Status"
                        :error="form.errors.is_active"
                    >
                        <select id="category-status" v-model="form.is_active">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </Field>

                    <Field
                        id="category-image"
                        label="Image"
                        :error="form.errors.image_path"
                        hint="Upload a new image from here or choose an existing library image."
                    >
                        <MediaPicker
                            id="category-image"
                            v-model="form.image_path"
                            file-input
                            label="Choose or upload image"
                            @update:model-value="form.remove_image = false"
                        />
                    </Field>
                </div>

                <section v-if="form.type === 'category'" class="visibility-section" aria-labelledby="visibility-title">
                    <div class="visibility-heading">
                        <span class="visibility-heading-icon"><Eye :size="19" aria-hidden="true" /></span>
                        <div>
                            <h2 id="visibility-title">Storefront visibility</h2>
                            <p>Choose where this category appears in your store.</p>
                        </div>
                    </div>
                    <div class="visibility-options">
                        <div v-for="option in visibilityOptions" :key="option.key" class="visibility-option" :class="{ 'is-enabled': form[option.key] }">
                            <div class="visibility-control">
                                <button type="button" role="switch" :aria-checked="form[option.key]"
                                    :aria-labelledby="`${option.key}-label`" :aria-describedby="`${option.key}-description`"
                                    class="visibility-switch" :class="{ enabled: form[option.key] }" :disabled="form.processing"
                                    @click="form[option.key] = !form[option.key]">
                                    <span class="switch-knob"></span>
                                </button>
                            </div>
                            <span class="visibility-icon"><component :is="option.icon" :size="17" :stroke-width="1.7" aria-hidden="true" /></span>
                            <div class="visibility-copy">
                                <span :id="`${option.key}-label`" class="visibility-label">{{ option.label }}</span>
                                <p :id="`${option.key}-description`">{{ option.description }}</p>
                                <small v-if="form.errors[option.key]" class="field-error" role="alert">{{ form.errors[option.key] }}</small>
                            </div>

                        </div>
                    </div>
                    <p class="visibility-note">Changes apply when you save the category.</p>
                </section>

                <div v-if="imageUrl" class="category-image-preview">
                    <img :src="imageUrl" alt="Category image preview" />
                    <button
                        type="button"
                        class="icon-button danger"
                        aria-label="Remove image"
                        title="Remove image"
                        @click="resetImage"
                    >
                        <ImagePlus :size="16" />
                    </button>
                </div>
                <label
                    v-if="record?.image && !form.image_path"
                    class="remove-current-image"
                >
                    <input v-model="form.remove_image" type="checkbox" />
                    Remove current image
                </label>

                <div class="category-form-actions">
                    <Link href="/admin/categories" class="btn secondary">
                        Cancel
                    </Link>
                    <button class="btn primary" :disabled="form.processing">
                        <Save :size="16" />
                        {{ form.processing ? "Saving…" : "Save category" }}
                    </button>
                </div>
            </section>
        </form>
    </AdminLayout>
</template>

<style scoped>
.visibility-section { margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--line, #e9ede7); }
.visibility-heading { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
.visibility-heading-icon { display: grid; place-items: center; width: 40px; height: 40px; flex-shrink: 0; border: 1px solid #e1e9df; border-radius: 12px; background: var(--cream, #eef2e8); color: var(--green, #23694f); }
.visibility-heading h2 { margin: 0; color: var(--dark, #183d30); font-size: 15px; font-weight: 700; }
.visibility-heading p { margin: 4px 0 0; color: #738279; font-size: 12px; line-height: 1.5; }
.visibility-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 22px 28px; }
.visibility-option { display: grid; grid-template-columns: 32px 18px minmax(0, 1fr); gap: 8px; align-items: start; padding: 8px 0; }
.visibility-icon { display: flex; align-items: center; height: 20px; color: #294334; }
.visibility-copy { min-width: 0; }
.visibility-label { color: #2c4437; font-size: 12px; font-weight: 600; line-height: 20px; }
.visibility-copy p { margin: 5px 0 0; color: #758379; font-size: 11px; line-height: 1.6; }
.visibility-control { padding-top: 3px; }
.visibility-switch { position: relative; display: block; width: 30px; height: 16px; padding: 0; border: 1px solid #d7ddd7; border-radius: 99px; background: #fff; cursor: pointer; transition: background 180ms, border-color 180ms; }
.visibility-switch.enabled { border-color: var(--green, #23694f); background: var(--green, #23694f); }
.visibility-switch:hover { border-color: var(--green, #23694f); }
.visibility-switch:focus-visible { outline: 3px solid #b9d4bf; outline-offset: 3px; }
.visibility-switch:disabled { cursor: wait; opacity: 0.65; }
.switch-knob { position: absolute; top: 2px; left: 2px; width: 10px; height: 10px; border-radius: 50%; background: #bfc6bf; transition: transform 180ms, background 180ms; }
.visibility-switch.enabled .switch-knob { transform: translateX(14px); background: white; }
.visibility-note { margin: 16px 0 0; color: #839087; font-size: 11px; }
@media (max-width: 540px) { .visibility-options { grid-template-columns: 1fr; gap: 12px; } }
@media (prefers-reduced-motion: reduce) { .visibility-switch, .switch-knob { transition: none; } }
.category-form {
    max-width: 900px;
}
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}
.form-grid :deep(input),
.form-grid :deep(select) {
    width: 100%;
    min-height: 44px;
}
.form-grid :deep(select:disabled) {
    background: #f2f5f6;
    color: #738391;
    cursor: not-allowed;
}
.category-image-preview {
    position: relative;
    display: inline-grid;
    width: min(240px, 100%);
    aspect-ratio: 1.35;
    margin-top: 20px;
    overflow: hidden;
    place-items: center;
    border: 1px solid #e3e9ed;
    border-radius: 10px;
    background: #f7f9fa;
}
.category-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.category-image-preview button {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #fff;
}
.remove-current-image {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 14px;
    color: #8b514a;
    font-size: 12px;
}
.category-form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 26px;
    padding-top: 20px;
    border-top: 1px solid #e8edf0;
}
@media (max-width: 620px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
