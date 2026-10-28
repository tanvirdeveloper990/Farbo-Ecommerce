<script setup>
import { computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ArrowLeft, ImagePlus, Plus, Save, Trash2, X } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";
import SummernoteEditor from "../Components/SummernoteEditor.vue";
import MediaPicker from "../Components/MediaPicker.vue";

const props = defineProps({
    product: Object,
    categories: Array,
    brands: Array,
    colors: Array,
    sizes: Array,
});

let keyCounter = 0;
const newKey = () => ++keyCounter;
const form = useForm({
    name: props.product?.name || "",
    category_id: props.product?.category_id || "",
    subcategory_id: props.product?.subcategory_id || "",
    child_category_id: props.product?.child_category_id || "",
    brand_id: props.product?.brand_id || "",
    regular_price: props.product?.regular_price || "",
    sale_price: props.product?.sale_price || "",
    unit: props.product?.unit || "",
    stock: props.product?.stock ?? 0,
    description: props.product?.description || "",
    new_arrival: props.product?.new_arrival ?? false,
    status: props.product?.status ?? false,
    featured_image_one: null,
    featured_image_two: null,
    featured_image_one_path: "",
    featured_image_two_path: "",
    remove_featured_image_one: false,
    remove_featured_image_two: false,
    product_images: [],
    product_image_paths: [],
    remove_product_image_ids: [],
    variants:
        props.product?.variants.map((variant) => ({
            key: newKey(),
            color_id: variant.color_id || "",
            size_id: variant.size_id || "",
            stock: variant.stock,
            price: variant.price,
            image_path: variant.image_path || "",
        })) || [],
});
const selectedColorIds = ref([
    ...new Set(
        (props.product?.variants || [])
            .map((variant) => variant.color_id)
            .filter(Boolean),
    ),
]);
const selectedSizeIds = ref([
    ...new Set(
        (props.product?.variants || [])
            .map((variant) => variant.size_id)
            .filter(Boolean),
    ),
]);
const mediaGalleryPreviews = ref([]);
const savedGalleryCount = computed(
    () =>
        (props.product?.images.length || 0) -
        form.remove_product_image_ids.length,
);
const galleryCount = computed(
    () =>
        savedGalleryCount.value +
        form.product_images.length +
        form.product_image_paths.length,
);
const availableSubcategories = computed(
    () =>
        props.categories.find(
            (category) => Number(category.id) === Number(form.category_id),
        )?.subcategories || [],
);
const availableChildCategories = computed(
    () =>
        availableSubcategories.value.find(
            (subcategory) =>
                Number(subcategory.id) === Number(form.subcategory_id),
        )?.children || [],
);

function changeCategory() {
    if (
        !availableSubcategories.value.some(
            (subcategory) =>
                Number(subcategory.id) === Number(form.subcategory_id),
        )
    )
        form.subcategory_id = "";
    changeSubcategory();
}

function changeSubcategory() {
    if (
        !availableChildCategories.value.some(
            (child) => Number(child.id) === Number(form.child_category_id),
        )
    )
        form.child_category_id = "";
}

function selectGalleryMedia(urls) {
    form.product_image_paths = urls.slice(
        0,
        Math.max(0, 10 - savedGalleryCount.value - form.product_images.length),
    );
    mediaGalleryPreviews.value = form.product_image_paths.map((url, index) => ({
        key: `media-${index}-${url}`,
        url,
        name: url.split("/").pop() || "Media image",
    }));
}

function removeMediaGalleryImage(index) {
    form.product_image_paths.splice(index, 1);
    mediaGalleryPreviews.value.splice(index, 1);
}

function removeSavedImage(id) {
    form.remove_product_image_ids = form.remove_product_image_ids.includes(id)
        ? form.remove_product_image_ids.filter((imageId) => imageId !== id)
        : [...form.remove_product_image_ids, id];
}

function variantKey(colorId, sizeId) {
    return `${colorId || ""}-${sizeId || ""}`;
}

function generateVariants() {
    const currentVariants = new Map(
        form.variants.map((variant) => [
            variantKey(variant.color_id, variant.size_id),
            variant,
        ]),
    );
    const colorIds = selectedColorIds.value;
    const sizeIds = selectedSizeIds.value;
    const combinations =
        colorIds.length && sizeIds.length
            ? colorIds.flatMap((colorId) =>
                  sizeIds.map((sizeId) => [colorId, sizeId]),
              )
            : colorIds.length
              ? colorIds.map((colorId) => [colorId, ""])
              : sizeIds.map((sizeId) => ["", sizeId]);

    form.variants = combinations.map(([colorId, sizeId]) => {
        const existing = currentVariants.get(variantKey(colorId, sizeId));

        return (
            existing || {
                key: newKey(),
                color_id: colorId,
                size_id: sizeId,
                stock: 0,
                price: form.sale_price || form.regular_price || "",
                image_path: "",
            }
        );
    });
}

function variantLabel(variant) {
    return [
        props.colors.find(
            (color) => Number(color.id) === Number(variant.color_id),
        )?.name,
        props.sizes.find((size) => Number(size.id) === Number(variant.size_id))
            ?.name,
    ]
        .filter(Boolean)
        .join(" / ");
}

function save() {
    form.post(
        "/admin/products" + (props.product ? "/" + props.product.id : ""),
        {
            forceFormData: true,
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <Head :title="product ? 'Edit product' : 'Add product'" />
    <AdminLayout :title="product ? 'Edit product' : 'Add product'">
        <Link href="/admin/products" class="back-link">
            <ArrowLeft :size="16" />All products
        </Link>
        <div class="page-heading">
            <div>
                <span class="eyebrow">BUILD YOUR COLLECTION</span>
                <h1>{{ product ? "Edit product" : "Add product" }}</h1>
                <p>Add product details, options, and images for your store.</p>
            </div>
        </div>

        <form class="product-form" @submit.prevent="save">
            <div class="product-form-main">
                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <div>
                            <h3>Basic information</h3>
                            <p>
                                Name, pricing, inventory, and product
                                description.
                            </p>
                        </div>
                    </div>
                    <div class="product-information-grid">
                        <Field
                            id="product-name"
                            label="Product name"
                            :error="form.errors.name"
                        >
                            <input
                                id="product-name"
                                v-model="form.name"
                                required
                                maxlength="200"
                                placeholder="Enter product name"
                            />
                        </Field>
                        <div class="price-fields form-grid two-fields">
                            <Field
                                id="regular-price"
                                label="Regular price (৳)"
                                :error="form.errors.regular_price"
                            >
                                <input
                                    id="regular-price"
                                    v-model="form.regular_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                />
                            </Field>
                            <Field
                                id="sale-price"
                                label="Sale price (৳)"
                                :error="form.errors.sale_price"
                            >
                                <input
                                    id="sale-price"
                                    v-model="form.sale_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                />
                            </Field>
                            <Field
                                id="product-unit"
                                label="Unit"
                                :error="form.errors.unit"
                                hint="For example: pcs, kg, set"
                            >
                                <input
                                    id="product-unit"
                                    v-model="form.unit"
                                    maxlength="40"
                                    placeholder="pcs, kg, set"
                                />
                            </Field>
                            <Field
                                id="product-stock"
                                label="Stock"
                                :error="form.errors.stock"
                            >
                                <input
                                    id="product-stock"
                                    v-model="form.stock"
                                    type="number"
                                    min="0"
                                    step="1"
                                    required
                                />
                            </Field>
                        </div>
                        <Field
                            id="product-description"
                            label="Description"
                            :error="form.errors.description"
                        >
                            <SummernoteEditor
                                id="product-description"
                                v-model="form.description"
                                label="Product description"
                                placeholder="Describe the product, materials, fit, care instructions, and details…"
                                :disabled="form.processing"
                            />
                        </Field>
                    </div>
                </section>

                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <ImagePlus />
                        <div>
                            <h3>Product images</h3>
                            <p>
                                Add up to 10 gallery images. JPG, PNG, or WebP;
                                maximum 10 MB each.
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="
                            product?.images.length ||
                            mediaGalleryPreviews.length
                        "
                        class="gallery-grid"
                    >
                        <div
                            v-for="image in product?.images"
                            :key="'saved-' + image.id"
                            class="gallery-image"
                            :class="{
                                'image-removed':
                                    form.remove_product_image_ids.includes(
                                        image.id,
                                    ),
                            }"
                        >
                            <img
                                :src="image.path"
                                alt="Product gallery image"
                            />
                            <button
                                type="button"
                                :aria-label="
                                    form.remove_product_image_ids.includes(
                                        image.id,
                                    )
                                        ? 'Keep image'
                                        : 'Remove image'
                                "
                                @click="removeSavedImage(image.id)"
                            >
                                <X
                                    v-if="
                                        !form.remove_product_image_ids.includes(
                                            image.id,
                                        )
                                    "
                                    :size="16"
                                />
                                <span v-else>Undo</span>
                            </button>
                        </div>
                        <div
                            v-for="(image, index) in mediaGalleryPreviews"
                            :key="image.key"
                            class="gallery-image"
                        >
                            <img :src="image.url" :alt="image.name" />
                            <button
                                type="button"
                                aria-label="Remove selected library image"
                                @click="removeMediaGalleryImage(index)"
                            >
                                <X :size="16" />
                            </button>
                        </div>
                    </div>
                    <div class="gallery-controls">
                        <span>{{ galleryCount }} / 10 images</span>
                    </div>
                    <Field
                        id="product-images"
                        label="Product gallery images"
                        :error="
                            form.errors.product_images ||
                            form.errors['product_images.0']
                        "
                        hint="Select multiple images from the media library."
                    >
                        <MediaPicker
                            id="product-images"
                            v-model="form.product_image_paths"
                            :multiple="true"
                            :max-selections="10 - savedGalleryCount"
                            :disabled="galleryCount >= 10"
                            file-input
                            label="Choose product images"
                            @update:model-value="selectGalleryMedia"
                        />
                    </Field>
                    <p
                        v-if="form.errors.remove_product_image_ids"
                        class="field-error"
                    >
                        {{ form.errors.remove_product_image_ids }}
                    </p>
                </section>

                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <div>
                            <h3>Product variations</h3>
                            <p>
                                Choose colors and sizes to generate each
                                available combination.
                            </p>
                        </div>
                    </div>
                    <div class="variant-options">
                        <fieldset class="variant-option-group">
                            <legend>Colors</legend>
                            <label
                                v-for="color in colors"
                                :key="color.id"
                                class="variant-option"
                            >
                                <input
                                    v-model="selectedColorIds"
                                    type="checkbox"
                                    :value="color.id"
                                    :disabled="
                                        !color.is_active &&
                                        !selectedColorIds.includes(color.id)
                                    "
                                />
                                <span
                                    v-if="color.color"
                                    class="variant-color-swatch"
                                    :style="{ backgroundColor: color.color }"
                                />
                                <span>{{ color.name }}</span>
                                <small v-if="!color.is_active">Inactive</small>
                            </label>
                            <p v-if="!colors.length" class="variant-help">
                                Add colors in catalog settings to use them here.
                            </p>
                        </fieldset>
                        <fieldset class="variant-option-group">
                            <legend>Sizes</legend>
                            <label
                                v-for="size in sizes"
                                :key="size.id"
                                class="variant-option"
                            >
                                <input
                                    v-model="selectedSizeIds"
                                    type="checkbox"
                                    :value="size.id"
                                    :disabled="
                                        !size.is_active &&
                                        !selectedSizeIds.includes(size.id)
                                    "
                                />
                                <span>{{ size.name }}</span>
                                <small v-if="!size.is_active">Inactive</small>
                            </label>
                            <p v-if="!sizes.length" class="variant-help">
                                Add sizes in catalog settings to use them here.
                            </p>
                        </fieldset>
                    </div>
                    <div class="variant-generation">
                        <p>
                            {{
                                selectedColorIds.length &&
                                selectedSizeIds.length
                                    ? `${selectedColorIds.length} colors × ${selectedSizeIds.length} sizes`
                                    : selectedColorIds.length
                                      ? `${selectedColorIds.length} color variations`
                                      : selectedSizeIds.length
                                        ? `${selectedSizeIds.length} size variations`
                                        : "No options selected"
                            }}
                        </p>
                        <button
                            type="button"
                            class="btn secondary compact-btn"
                            @click="generateVariants"
                        >
                            <Plus :size="16" />Generate variations
                        </button>
                    </div>
                    <div v-if="!form.variants.length" class="variant-empty">
                        Select one or more colors or sizes, then generate
                        variations. Choose both to create every combination.
                    </div>
                    <div
                        v-for="(variant, index) in form.variants"
                        :key="variant.key"
                        class="variant-row"
                    >
                        <div class="variant-name">
                            <span>Variation {{ index + 1 }}</span>
                            <strong>{{ variantLabel(variant) }}</strong>
                        </div>
                        <Field
                            :id="'variant-stock-' + index"
                            label="Stock"
                            :error="form.errors['variants.' + index + '.stock']"
                        >
                            <input
                                :id="'variant-stock-' + index"
                                v-model="variant.stock"
                                type="number"
                                min="0"
                                required
                            />
                        </Field>
                        <Field
                            :id="'variant-price-' + index"
                            label="Price (৳)"
                            :error="form.errors['variants.' + index + '.price']"
                        >
                            <input
                                :id="'variant-price-' + index"
                                v-model="variant.price"
                                type="number"
                                min="0"
                                step="0.01"
                                :required="form.regular_price !== ''"
                            />
                        </Field>
                        <Field
                            :id="'variant-image-' + index"
                            class="variant-image-field"
                            label="Variant image"
                            :error="
                                form.errors['variants.' + index + '.image_path']
                            "
                        >
                            <div
                                v-if="variant.image_path"
                                class="variant-image-preview"
                            >
                                <img
                                    :src="variant.image_path"
                                    :alt="variantLabel(variant) + ' variant'"
                                />
                            </div>
                            <MediaPicker
                                :id="'variant-image-' + index"
                                v-model="variant.image_path"
                                file-input
                                label="Choose image"
                            />
                        </Field>
                        <button
                            type="button"
                            class="icon-button danger remove-variant"
                            :aria-label="'Remove variant ' + (index + 1)"
                            @click="form.variants.splice(index, 1)"
                        >
                            <Trash2 :size="17" />
                        </button>
                    </div>
                </section>
            </div>

            <div class="product-form-side">
                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <div>
                            <h3>Category &amp; brand</h3>
                            <p>Choose from the catalog you already created.</p>
                        </div>
                    </div>
                    <div class="side-fields">
                        <Field
                            id="product-category"
                            label="Category"
                            :error="form.errors.category_id"
                        >
                            <select
                                id="product-category"
                                v-model="form.category_id"
                                required
                                @change="changeCategory"
                            >
                                <option value="">Select category</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>
                        </Field>
                        <Field
                            id="product-subcategory"
                            label="Subcategory"
                            :error="form.errors.subcategory_id"
                        >
                            <select
                                id="product-subcategory"
                                v-model="form.subcategory_id"
                                :disabled="!availableSubcategories.length"
                                @change="changeSubcategory"
                            >
                                <option value="">Select subcategory</option>
                                <option
                                    v-for="subcategory in availableSubcategories"
                                    :key="subcategory.id"
                                    :value="subcategory.id"
                                >
                                    {{ subcategory.name }}
                                </option>
                            </select>
                        </Field>
                        <Field
                            id="product-child-category"
                            label="Child category"
                            :error="form.errors.child_category_id"
                        >
                            <select
                                id="product-child-category"
                                v-model="form.child_category_id"
                                :disabled="!availableChildCategories.length"
                            >
                                <option value="">Select child category</option>
                                <option
                                    v-for="child in availableChildCategories"
                                    :key="child.id"
                                    :value="child.id"
                                >
                                    {{ child.name }}
                                </option>
                            </select>
                        </Field>
                        <Field
                            id="product-brand"
                            label="Brand"
                            :error="form.errors.brand_id"
                        >
                            <select id="product-brand" v-model="form.brand_id">
                                <option value="">Select brand</option>
                                <option
                                    v-for="brand in brands"
                                    :key="brand.id"
                                    :value="brand.id"
                                >
                                    {{ brand.name
                                    }}{{ brand.is_active ? "" : " (inactive)" }}
                                </option>
                            </select>
                        </Field>
                    </div>
                </section>

                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <div>
                            <h3>Product collection</h3>
                            <p>
                                Show this product in the New Arrivals
                                collection.
                            </p>
                        </div>
                    </div>
                    <label class="check-row">
                        <input v-model="form.new_arrival" type="checkbox" />
                        <span>New Arrival</span>
                    </label>
                    <small v-if="form.errors.new_arrival" class="field-error">{{
                        form.errors.new_arrival
                    }}</small>
                </section>

                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <ImagePlus />
                        <div>
                            <h3>Featured images</h3>
                            <p>Optional images for highlighted placements.</p>
                        </div>
                    </div>
                    <div class="featured-grid">
                        <div v-for="slot in ['one', 'two']" :key="slot">
                            <div class="featured-preview">
                                <img
                                    v-if="
                                        form[
                                            'featured_image_' + slot + '_path'
                                        ] ||
                                        (!form[
                                            'remove_featured_image_' + slot
                                        ] &&
                                            product?.['featured_image_' + slot])
                                    "
                                    :src="
                                        form[
                                            'featured_image_' + slot + '_path'
                                        ] || product['featured_image_' + slot]
                                    "
                                    :alt="'Featured image ' + slot"
                                />
                                <span v-else
                                    ><ImagePlus :size="22" />Featured image
                                    {{ slot }}</span
                                >
                            </div>
                            <Field
                                :id="'featured-image-' + slot"
                                :label="
                                    'Featured image ' +
                                    (slot === 'one' ? 'one' : 'two')
                                "
                                :error="form.errors['featured_image_' + slot]"
                                hint="Choose from the media library. Upload new files from Media."
                            >
                                <MediaPicker
                                    :id="'featured-image-' + slot"
                                    v-model="
                                        form['featured_image_' + slot + '_path']
                                    "
                                    file-input
                                    :label="'Choose featured image ' + slot"
                                    @update:model-value="
                                        form['featured_image_' + slot] = null;
                                        form['remove_featured_image_' + slot] =
                                            false;
                                    "
                                />
                            </Field>
                            <label
                                v-if="
                                    product?.['featured_image_' + slot] &&
                                    !form['featured_image_' + slot] &&
                                    !form['featured_image_' + slot + '_path']
                                "
                                class="remove-image-check"
                            >
                                <input
                                    v-model="
                                        form['remove_featured_image_' + slot]
                                    "
                                    type="checkbox"
                                />Remove current image
                            </label>
                        </div>
                    </div>
                </section>

                <section class="panel form-panel">
                    <div class="form-panel-title">
                        <div>
                            <h3>Status</h3>
                            <p>
                                Only active products are available to customers.
                            </p>
                        </div>
                    </div>
                    <Field
                        id="product-status"
                        label="Product status"
                        :error="form.errors.status"
                    >
                        <select id="product-status" v-model="form.status">
                            <option :value="true">Active</option>
                            <option :value="false">Inactive</option>
                        </select>
                    </Field>
                </section>

                <button
                    class="btn primary save-product"
                    :disabled="form.processing"
                >
                    <Save :size="17" />{{
                        form.processing
                            ? "Saving…"
                            : product
                              ? "Update product"
                              : "Create product"
                    }}
                </button>
            </div>
        </form>
    </AdminLayout>
</template>

<style scoped>
.product-form {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(290px, 0.9fr);
    gap: 22px;
    align-items: start;
}
.product-form-main,
.product-form-side {
    display: grid;
    gap: 20px;
    min-width: 0;
}
.form-panel-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 22px;
}
.product-form :deep(.form-panel) {
    border: 1px solid #e5ebe6;
    border-radius: 15px;
    box-shadow: 0 8px 28px rgb(29 55 37 / 5%);
}
.product-form :deep(.form-panel-title) {
    padding-bottom: 16px;
    border-bottom: 1px solid #edf1ed;
}
.product-form :deep(.field label) {
    margin-bottom: 7px;
    color: #506354;
    font-size: 12px;
    font-weight: 600;
}
.product-form :deep(.field input:not([type="file"])),
.product-form :deep(.field select),
.product-form :deep(.field textarea) {
    min-height: 44px;
    border: 1px solid #dce5df;
    border-radius: 9px;
    background: #fbfcfa;
    transition:
        border-color 0.18s ease,
        box-shadow 0.18s ease,
        background 0.18s ease;
}
.product-form :deep(.field input:not([type="file"]):focus),
.product-form :deep(.field select:focus),
.product-form :deep(.field textarea:focus) {
    border-color: #66806c;
    background: #fff;
    box-shadow: 0 0 0 3px rgb(83 125 93 / 12%);
    outline: none;
}
.product-form :deep(.field textarea) {
    padding: 12px;
}
.price-fields {
    padding: 16px;
    border: 1px solid #e8eee8;
    border-radius: 12px;
    background: #f8faf7;
}
.product-form :deep(.policy-editor .note-editor) {
    border-color: #dce5df;
    border-radius: 11px;
}
.product-form :deep(.policy-editor .note-editable) {
    min-height: 240px;
}
.form-panel-title h3 {
    color: #344d3b;
    font-size: 15px;
    font-weight: 600;
}
.form-panel-title p {
    margin-top: 4px;
    color: #859188;
    font-size: 12px;
    line-height: 1.5;
}
.form-grid {
    display: grid;
    gap: 16px;
}
.product-information-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 0;
}
.two-fields {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}
.price-fields {
    border: 1px solid #e8eee8;
    border-radius: 12px;
    padding: 18px 16px 0;
    background: #f8faf7;
}
.product-form input,
.product-form select,
.product-form textarea {
    max-width: 100%;
}
.product-form textarea {
    width: 100%;
    resize: vertical;
    line-height: 1.7;
}
.side-fields {
    display: grid;
    gap: 15px;
}
.compact-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.featured-grid {
    display: grid;
    gap: 20px;
}
.featured-preview {
    display: grid;
    height: 135px;
    margin-bottom: 12px;
    place-items: center;
    overflow: hidden;
    border: 1px dashed #d2ded6;
    border-radius: 10px;
    background: #f7f9f6;
    color: #849184;
    font-size: 12px;
}
.featured-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.featured-preview span {
    display: grid;
    justify-items: center;
    gap: 8px;
}
.remove-image-check,
.check-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 9px;
    color: #68786c;
    font-size: 12px;
}
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 12px;
    margin-bottom: 18px;
}
.gallery-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-top: 14px;
    border-top: 1px solid #edf1ed;
}
.gallery-controls > span {
    color: #7b897e;
    font-size: 12px;
    font-weight: 600;
}
.gallery-image {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    border: 1px solid #e1e9e3;
    border-radius: 10px;
    background: #f5f7f5;
}
.gallery-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.gallery-image button {
    position: absolute;
    top: 6px;
    right: 6px;
    display: grid;
    min-width: 26px;
    height: 26px;
    padding: 0 5px;
    place-items: center;
    border: 0;
    border-radius: 7px;
    background: #b42318;
    color: #fff;
    font-size: 10px;
    cursor: pointer;
}
.image-removed img {
    opacity: 0.3;
}
.variant-empty {
    padding: 16px;
    border: 1px dashed #dce5df;
    border-radius: 9px;
    color: #849184;
    font-size: 13px;
}
.variant-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}
.variant-option-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-width: 0;
    margin: 0;
    padding: 14px;
    border: 1px solid #e5ebe6;
    border-radius: 11px;
}
.variant-option-group legend {
    padding: 0 5px;
    color: #506354;
    font-size: 12px;
    font-weight: 600;
}
.variant-option {
    display: inline-flex;
    min-height: 34px;
    align-items: center;
    gap: 7px;
    padding: 5px 9px;
    border: 1px solid #e2e9e3;
    border-radius: 8px;
    background: #fafcf9;
    color: #536458;
    font-size: 12px;
    cursor: pointer;
}
.variant-option input {
    accent-color: #285f48;
}
.variant-option small {
    color: #9a6b29;
    font-size: 10px;
}
.variant-color-swatch {
    width: 13px;
    height: 13px;
    border: 1px solid rgb(0 0 0 / 12%);
    border-radius: 50%;
}
.variant-help {
    width: 100%;
    color: #849184;
    font-size: 11px;
}
.variant-generation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 14px;
}
.variant-generation p {
    color: #718075;
    font-size: 12px;
}
.check-row {
    width: fit-content;
    padding: 12px 14px;
    border: 1px solid #e1e9e1;
    border-radius: 10px;
    background: #f8faf7;
    cursor: pointer;
}
.check-row input {
    accent-color: #285f48;
}
.variant-row {
    display: grid;
    grid-template-columns:
        minmax(130px, 0.8fr) minmax(85px, 0.45fr) minmax(115px, 0.6fr)
        minmax(180px, 1fr) 36px;
    gap: 12px;
    align-items: end;
    padding: 16px 0;
    border-top: 1px solid #edf0e9;
}
.variant-name {
    display: grid;
    gap: 5px;
    align-self: center;
    overflow-wrap: anywhere;
}
.variant-name span {
    color: #89958c;
    font-size: 11px;
}
.variant-name strong {
    color: #344d3b;
    font-size: 13px;
    font-weight: 600;
}
.variant-image-preview {
    width: 48px;
    height: 48px;
    margin-bottom: 7px;
    overflow: hidden;
    border: 1px solid #e1e9e3;
    border-radius: 8px;
    background: #f5f7f5;
}
.variant-image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.remove-variant {
    margin-bottom: 3px;
}
.save-product {
    display: inline-flex;
    width: 100%;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    gap: 9px;
}
@media (max-width: 1050px) {
    .product-form {
        grid-template-columns: 1fr;
    }
    .product-form-side {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .save-product {
        grid-column: 1 / -1;
    }
}
@media (max-width: 700px) {
    .price-fields {
        grid-template-columns: 1fr;
    }

    .product-form-side {
        grid-template-columns: 1fr;
    }
    .variant-options {
        grid-template-columns: 1fr;
    }
    .variant-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .variant-name {
        grid-column: 1 / -1;
    }
    .variant-row :deep(.variant-image-field) {
        grid-column: 1 / -1;
    }
    .remove-variant {
        justify-self: end;
    }
}
</style>
