<script setup>
import { computed, nextTick, onBeforeUnmount, provide, ref, watch } from "vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import {
    ChevronLeft,
    ChevronRight,
    Check,
    Maximize2,
    Minus,
    Plus,
    ShoppingBag,
    Truck,
    X,
} from "@lucide/vue";
import StorefrontLayout from "./StorefrontLayout.vue";
import ProductRail from "./Components/ProductRail.vue";
import { createStorefrontContext } from "./storefrontContext";

const props = defineProps({
    product: Object,
    relatedProducts: { type: Array, default: () => [] },
});
const storefront = createStorefrontContext();
provide("storefront", storefront);
const galleryImage = ref(props.product.images[0] || "");
const lightboxOpen = ref(false);
const lightboxImage = ref("");
const imageOverride = ref("");
const zoomOrigin = ref("50% 50%");
const lightboxElement = ref(null);
const zoomButton = ref(null);
let previousBodyOverflow = "";
let bodyScrollLocked = false;
const selectedColorId = ref(null);
const selectedSizeId = ref(null);
const quantity = ref(1);
const variants = computed(() => props.product.variants || []);
const colorOptions = computed(() => {
    const colors = new Map();
    for (const option of variants.value) {
        if (option.colorId && !colors.has(option.colorId)) {
            colors.set(option.colorId, {
                id: option.colorId,
                name: option.color,
                code: option.colorCode,
            });
        }
    }
    return [...colors.values()];
});
const sizeOptions = computed(() => {
    const options = new Map();
    for (const option of variants.value) {
        if (
            option.sizeId &&
            (!colorOptions.value.length ||
                option.colorId === selectedColorId.value) &&
            !options.has(option.sizeId)
        ) {
            options.set(option.sizeId, {
                id: option.sizeId,
                name: option.size,
            });
        }
    }
    return [...options.values()];
});
const variant = computed(() => {
    if (!variants.value.length) return null;
    if (colorOptions.value.length && selectedColorId.value === null)
        return null;
    if (sizeOptions.value.length && selectedSizeId.value === null) return null;
    return (
        variants.value.find(
            (item) =>
                item.colorId === selectedColorId.value &&
                item.sizeId === selectedSizeId.value,
        ) || null
    );
});
const activeImage = computed(
    () => imageOverride.value || variant.value?.image || galleryImage.value,
);
const lightboxImages = computed(() => [
    ...new Set([activeImage.value, ...props.product.images].filter(Boolean)),
]);
const settings = computed(() => usePage().props.storeSettings || {});
const price = computed(() => variant.value?.price ?? props.product.price);
const stock = computed(
    () =>
        variant.value?.stock ??
        (variants.value.length ? null : props.product.stock),
);
watch([variant, stock], () => {
    quantity.value = Math.min(quantity.value, Math.max(stock.value, 1));
});
watch(selectedColorId, () => {
    selectedSizeId.value = null;
    imageOverride.value = "";
});
watch(selectedSizeId, () => {
    imageOverride.value = "";
});
watch(variant, () => {
    imageOverride.value = "";
});
const productUrl = computed(
    () => `${window.location.origin}/product/${props.product.id}`,
);
const whatsappUrl = computed(() => {
    const phone = (settings.value.phone_one || "").replace(/\D/g, "");
    const message = encodeURIComponent(
        `I'm interested in ${props.product.name}: ${productUrl.value}`,
    );
    return phone
        ? `https://wa.me/${phone}?text=${message}`
        : `https://wa.me/?text=${message}`;
});
function addToCart(goToCheckout = false) {
    if (props.product.variants.length && !variant.value) {
        storefront.notify("Choose a product option first.");
        return;
    }
    const added = storefront.addToCart(
        { ...props.product, image: activeImage.value },
        variant.value,
        quantity.value,
    );
    if (added && goToCheckout) router.visit("/checkout");
}
function updateZoomOrigin(event) {
    const bounds = event.currentTarget.getBoundingClientRect();
    const x = Math.max(
        0,
        Math.min(100, ((event.clientX - bounds.left) / bounds.width) * 100),
    );
    const y = Math.max(
        0,
        Math.min(100, ((event.clientY - bounds.top) / bounds.height) * 100),
    );
    zoomOrigin.value = `${x}% ${y}%`;
}
function openLightbox() {
    lightboxImage.value = activeImage.value;
    lightboxOpen.value = true;
}
function showImage(index) {
    const images = lightboxImages.value;
    if (!images.length) return;
    const nextIndex = (index + images.length) % images.length;
    lightboxImage.value = images[nextIndex];
}
function handleLightboxKeydown(event) {
    if (event.key === "Escape") lightboxOpen.value = false;
    if (event.key === "ArrowLeft") {
        showImage(lightboxImages.value.indexOf(lightboxImage.value) - 1);
    }
    if (event.key === "ArrowRight") {
        showImage(lightboxImages.value.indexOf(lightboxImage.value) + 1);
    }
}
watch(lightboxOpen, async (open) => {
    if (open) {
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";
        bodyScrollLocked = true;
        await nextTick();
        lightboxElement.value?.focus();
    } else if (bodyScrollLocked) {
        document.body.style.overflow = previousBodyOverflow;
        bodyScrollLocked = false;
        zoomButton.value?.focus();
    }
});
onBeforeUnmount(() => {
    if (bodyScrollLocked) {
        document.body.style.overflow = previousBodyOverflow;
    }
});
async function share() {
    if (navigator.share) {
        await navigator.share({
            title: props.product.name,
            url: productUrl.value,
        });
        return;
    }
    await navigator.clipboard.writeText(productUrl.value);
    storefront.notify("Product link copied.");
}
</script>

<template>
    <Head :title="`${product.name} | ${settings.company_name || 'Store'}`" />
    <StorefrontLayout>
        <main class="product-detail-page">
            <div class="detail-breadcrumb">
                <Link href="/">Home</Link>
                <span>/</span>
                <Link
                    v-if="product.categoryId"
                    :href="`/categories/${encodeURIComponent(product.categorySlug || product.categoryId)}`"
                >
                    {{ product.category }}
                </Link>
                <span v-else>{{ product.category }}</span>
                <span>/</span>
                <span>{{ product.name }}</span>
            </div>

            <div class="product-detail-grid">
                <section class="gallery">
                    <div class="main-image" @mousemove="updateZoomOrigin">
                        <img
                            v-if="activeImage"
                            :src="activeImage"
                            :alt="product.name"
                            :style="{ transformOrigin: zoomOrigin }"
                            @click="openLightbox"
                        />
                        <span v-else>Product image coming soon</span>
                        <button
                            v-if="activeImage"
                            type="button"
                            class="zoom-open"
                            ref="zoomButton"
                            aria-label="Open product image zoom"
                            title="View larger image"
                            @click="openLightbox"
                        >
                            <Maximize2 :size="17" />
                            <span>View larger</span>
                        </button>
                        <span v-if="activeImage" class="zoom-hint">
                            <Maximize2 :size="14" /> Hover to zoom
                        </span>
                    </div>
                    <div
                        v-if="product.images.length > 1"
                        class="thumbnail-list"
                    >
                        <button
                            v-for="image in product.images"
                            :key="image"
                            :class="{ selected: image === activeImage }"
                            @click="
                                imageOverride = image;
                                galleryImage = image;
                            "
                            :aria-label="`View ${product.name} image`"
                            :aria-pressed="image === activeImage"
                        >
                            <img :src="image" :alt="product.name" />
                        </button>
                    </div>
                </section>

                <section class="product-info">
                    <p class="eyebrow">{{ product.category }}</p>
                    <h1>{{ product.name }}</h1>
                    <p v-if="product.brand" class="brand">
                        {{ product.brand }}
                    </p>
                    <div
                        class="availability"
                        :class="{
                            unavailable: stock !== null && stock < 1,
                            pending: variants.length && !variant,
                        }"
                    >
                        <span></span>
                        {{
                            variants.length && !variant
                                ? "Select options"
                                : stock > 0
                                  ? "In stock"
                                  : "Out of stock"
                        }}
                    </div>
                    <div class="price-row">
                        <strong
                            >৳{{
                                Number(price).toLocaleString("en-BD", {
                                    minimumFractionDigits: 2,
                                })
                            }}</strong
                        >
                        <del v-if="product.oldPrice && !variant"
                            >৳{{
                                Number(product.oldPrice).toLocaleString(
                                    "en-BD",
                                    { minimumFractionDigits: 2 },
                                )
                            }}</del
                        >
                    </div>
                    <p class="stock-copy">
                        {{
                            variants.length && !variant
                                ? "Select your options to check availability"
                                : `${stock} available`
                        }}
                    </p>

                    <div v-if="colorOptions.length" class="variant-picker">
                        <div class="variant-picker-heading">
                            <span>Color</span>
                            <strong>{{
                                colorOptions.find(
                                    (option) => option.id === selectedColorId,
                                )?.name || "Select a color"
                            }}</strong>
                        </div>
                        <div class="variant-swatches">
                            <button
                                v-for="option in colorOptions"
                                :key="option.id"
                                type="button"
                                class="color-swatch"
                                :class="{
                                    selected: selectedColorId === option.id,
                                }"
                                :style="{
                                    '--swatch-color': option.code || '#ded8d2',
                                }"
                                :aria-label="`Select ${option.name} color`"
                                :aria-pressed="selectedColorId === option.id"
                                @click="selectedColorId = option.id"
                            >
                                <Check
                                    v-if="selectedColorId === option.id"
                                    :size="17"
                                    aria-hidden="true"
                                />
                            </button>
                        </div>
                    </div>
                    <div v-if="sizeOptions.length" class="variant-picker">
                        <div class="variant-picker-heading">
                            <span>Size</span>
                            <strong>{{
                                sizeOptions.find(
                                    (option) => option.id === selectedSizeId,
                                )?.name || "Select a size"
                            }}</strong>
                        </div>
                        <div class="variant-swatches size-swatches">
                            <button
                                v-for="option in sizeOptions"
                                :key="option.id"
                                type="button"
                                class="size-swatch"
                                :class="{
                                    selected: selectedSizeId === option.id,
                                }"
                                :aria-pressed="selectedSizeId === option.id"
                                @click="selectedSizeId = option.id"
                            >
                                <Check
                                    v-if="selectedSizeId === option.id"
                                    :size="14"
                                    aria-hidden="true"
                                />
                                <span>{{ option.name }}</span>
                            </button>
                        </div>
                    </div>
                    <p
                        v-if="variants.length && !variant"
                        class="variant-selection-hint"
                    >
                        Select
                        {{ colorOptions.length ? "a color" : "a size" }}
                        <template
                            v-if="colorOptions.length && sizeOptions.length"
                        >
                            and a size
                        </template>
                        to see price and availability.
                    </p>

                    <div class="quantity-row">
                        <span>Quantity</span>
                        <div class="quantity-control">
                            <button
                                type="button"
                                aria-label="Decrease quantity"
                                @click="quantity = Math.max(1, quantity - 1)"
                            >
                                <Minus :size="14" />
                            </button>
                            <span>{{ quantity }}</span>
                            <button
                                type="button"
                                aria-label="Increase quantity"
                                :disabled="quantity >= stock"
                                @click="
                                    quantity = Math.min(stock, quantity + 1)
                                "
                            >
                                <Plus :size="14" />
                            </button>
                        </div>
                        <small>Minimum 1 piece</small>
                    </div>

                    <div class="action-row">
                        <button
                            class="button-primary"
                            type="button"
                            :disabled="stock < 1"
                            @click="addToCart(true)"
                        >
                            <ShoppingBag :size="16" />Order now
                        </button>
                        <button
                            class="button-outline"
                            type="button"
                            :disabled="stock < 1"
                            @click="addToCart()"
                        >
                            Add to cart
                        </button>
                    </div>
                    <div class="action-row secondary-actions">
                        <a
                            class="whatsapp-button"
                            :href="whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            >WhatsApp</a
                        >
                        <a
                            class="messenger-button"
                            :href="settings.facebook || 'https://m.me/'"
                            target="_blank"
                            rel="noopener noreferrer"
                            >Messenger</a
                        >
                    </div>
                    <div class="delivery-note">
                        <Truck :size="17" />
                        <span
                            >Delivery available across Bangladesh. View the
                            current charge during checkout.</span
                        >
                    </div>
                    <button class="share-button" type="button" @click="share">
                        Share product
                    </button>
                </section>
            </div>

            <section v-if="product.description" class="description-section">
                <h2>Product details</h2>
                <div
                    class="product-description"
                    v-html="product.description"
                ></div>
            </section>

            <ProductRail
                v-if="relatedProducts.length"
                title="You may also like"
                :products="relatedProducts"
                :href="
                    product.categoryId
                        ? `/categories/${encodeURIComponent(product.categorySlug || product.categoryId)}`
                        : '/#collections'
                "
            />
        </main>
        <Teleport to="body">
            <div
                v-if="lightboxOpen && lightboxImage"
                ref="lightboxElement"
                tabindex="-1"
                class="image-lightbox"
                role="dialog"
                aria-modal="true"
                aria-label="Product image viewer"
                @click.self="lightboxOpen = false"
                @keydown="handleLightboxKeydown"
            >
                <button
                    type="button"
                    class="lightbox-close"
                    aria-label="Close image viewer"
                    @click="lightboxOpen = false"
                >
                    <X :size="22" />
                </button>
                <button
                    v-if="lightboxImages.length > 1"
                    type="button"
                    class="lightbox-arrow previous"
                    aria-label="Previous product image"
                    @click="
                        showImage(lightboxImages.indexOf(lightboxImage) - 1)
                    "
                >
                    <ChevronLeft :size="24" />
                </button>
                <div class="lightbox-image-wrap" @mousemove="updateZoomOrigin">
                    <img
                        :src="lightboxImage"
                        :alt="product.name"
                        :style="{ transformOrigin: zoomOrigin }"
                    />
                </div>
                <button
                    v-if="lightboxImages.length > 1"
                    type="button"
                    class="lightbox-arrow next"
                    aria-label="Next product image"
                    @click="
                        showImage(lightboxImages.indexOf(lightboxImage) + 1)
                    "
                >
                    <ChevronRight :size="24" />
                </button>
                <div class="lightbox-caption">
                    <span>{{ product.name }}</span>
                    <span>
                        {{ lightboxImages.indexOf(lightboxImage) + 1 }} /
                        {{ lightboxImages.length }}
                    </span>
                </div>
            </div>
        </Teleport>
    </StorefrontLayout>
</template>

<style scoped>
.product-detail-page {
    box-sizing: border-box;
    width: min(1440px, 100%);
    margin: 0 auto;
    padding: 28px clamp(18px, 3.2vw, 48px) 70px;
}
.detail-breadcrumb {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 23px;
    color: #64748B;
    font-size: 11px;
}
.detail-breadcrumb a:hover {
    color: #E30613;
}
.product-detail-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr);
    gap: clamp(28px, 5vw, 72px);
    align-items: start;
}
.gallery {
    min-width: 0;
}
.main-image {
    position: relative;
    display: grid;
    min-height: 0;
    aspect-ratio: 4 / 4.55;
    place-items: center;
    overflow: hidden;
    border-radius: 3px;
    background: #F3F6FB;
    color: #64748B;
    font-size: 13px;
    cursor: zoom-in;
}
.main-image img {
    width: 100%;
    height: 100%;
    max-height: 620px;
    object-fit: contain;
    transition: transform 220ms ease;
}
.main-image:hover img,
.main-image:focus-within img {
    transform: scale(1.7);
}
.zoom-open,
.zoom-hint {
    position: absolute;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: 1px solid #ffffffb8;
    border-radius: 999px;
    background: #ffffffed;
    color: #0B2A5B;
    box-shadow: 0 4px 16px #0B2A5B1a;
    backdrop-filter: blur(8px);
}
.zoom-open {
    top: 13px;
    right: 13px;
    padding: 9px 12px;
    font-size: 11px;
    font-weight: 650;
}
.zoom-hint {
    bottom: 13px;
    left: 13px;
    padding: 7px 10px;
    color: #0B2A5B;
    font-size: 10px;
    pointer-events: none;
}
.thumbnail-list {
    display: flex;
    gap: 10px;
    margin-top: 14px;
    overflow-x: auto;
    padding-bottom: 3px;
}
.thumbnail-list button {
    flex: 0 0 62px;
    width: 62px;
    height: 72px;
    overflow: hidden;
    border: 1px solid #DCE4EF;
    border-radius: 9px;
    background: #F3F6FB;
}
.thumbnail-list button.selected {
    border: 2px solid #E30613;
}
.thumbnail-list img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.product-info {
    min-width: 0;
    padding: 0;
}
.eyebrow {
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.2em;
}
.product-info h1 {
    margin-top: 8px;
    color: #0B2A5B;
    font-size: clamp(23px, 3vw, 32px);
    font-weight: 700;
    line-height: 1.2;
}
.brand {
    margin-top: 5px;
    color: #64748B;
    font-size: 12px;
}
.availability {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    margin-top: 15px;
    border: 1px solid #b7ead2;
    border-radius: 99px;
    padding: 5px 10px;
    color: #18724e;
    font-size: 10px;
}
.availability span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #16a46b;
}
.availability.unavailable {
    border-color: #f4c2c2;
    color: #a12626;
}
.availability.pending {
    border-color: #DCE4EF;
    color: #64748B;
}
.availability.unavailable span {
    background: #c92a2a;
}
.price-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 15px;
    border-block: 1px solid #F3F6FB;
    padding: 14px 0;
}
.price-row strong {
    color: #E30613;
    font-size: 26px;
}
.price-row del {
    color: #64748B;
    font-size: 13px;
}
.stock-copy {
    margin-top: 12px;
    color: #64748B;
    font-size: 10px;
}
.option-field {
    display: grid;
    gap: 7px;
    margin-top: 19px;
    font-size: 11px;
}
.variant-picker {
    display: grid;
    gap: 11px;
    margin-top: 21px;
}
.variant-picker-heading {
    display: flex;
    align-items: baseline;
    gap: 9px;
    color: #0B2A5B;
    font-size: 12px;
    font-weight: 650;
}
.variant-picker-heading strong {
    color: #64748B;
    font-size: 11px;
    font-weight: 500;
}
.variant-swatches {
    display: flex;
    flex-wrap: wrap;
    gap: 11px;
}
.color-swatch {
    position: relative;
    display: grid;
    width: 39px;
    height: 39px;
    place-items: center;
    border: 2px solid #fff;
    border-radius: 50%;
    background: var(--swatch-color);
    box-shadow: 0 0 0 1px #DCE4EF;
    color: #fff;
    cursor: pointer;
    transition:
        box-shadow 160ms ease,
        transform 160ms ease;
}
.color-swatch:hover {
    transform: scale(1.06);
}
.color-swatch.selected {
    box-shadow: 0 0 0 2px #E30613;
}
.color-swatch svg {
    filter: drop-shadow(0 1px 2px #0009);
}
.size-swatch {
    display: inline-flex;
    min-width: 44px;
    height: 44px;
    align-items: center;
    justify-content: center;
    gap: 3px;
    border: 1px solid #DCE4EF;
    border-radius: 50%;
    background: #fff;
    padding: 0 8px;
    color: #0B2A5B;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition:
        border-color 160ms ease,
        background-color 160ms ease,
        color 160ms ease;
}
.size-swatch:hover {
    border-color: #E30613;
}
.size-swatch.selected {
    border-color: #E30613;
    background: #E30613;
    color: #fff;
}
.variant-selection-hint {
    margin-top: 11px;
    color: #64748b;
    font-size: 11px;
}
.option-field select {
    border: 1px solid #DCE4EF;
    border-radius: 8px;
    background: #fff;
    padding: 11px;
    font-size: 12px;
}
.quantity-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 18px;
    font-size: 11px;
}
.quantity-control {
    display: inline-flex;
    align-items: center;
    border: 1px solid #dce4ef;
    border-radius: 7px;
    background: #F3F6FB;
}
.quantity-control button {
    width: 34px;
    height: 34px;
}
.quantity-control span {
    min-width: 30px;
    text-align: center;
}
.quantity-control small,
.quantity-row small {
    color: #64748B;
    font-size: 10px;
}
.action-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 16px;
}
.action-row button,
.action-row a {
    display: flex;
    min-height: 43px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 700;
}
.button-primary {
    background: #E30613;
    color: white;
}
.button-primary:hover {
    background: #C10510;
}
.button-outline {
    border: 1px solid #0B2A5B;
    color: #0B2A5B;
}
.action-row button:disabled {
    cursor: not-allowed;
    opacity: 0.55;
}
.secondary-actions {
    margin-top: 8px;
}
.whatsapp-button {
    background: #20c76b;
    color: white;
}
.messenger-button {
    background: #171717;
    color: white;
}
.delivery-note {
    display: flex;
    gap: 8px;
    margin-top: 22px;
    border-top: 1px solid #dce4ef;
    padding-top: 15px;
    color: #64748B;
    font-size: 10px;
    line-height: 1.5;
}
.delivery-note svg {
    flex: 0 0 auto;
    color: #E30613;
}
.share-button {
    margin-top: 16px;
    color: #64748B;
    font-size: 11px;
    text-decoration: underline;
    text-underline-offset: 3px;
}
.description-section {
    margin-top: 34px;
    border-top: 1px solid #dce4ef;
    padding: 24px 0;
}
.description-section h2 {
    border-bottom: 1px solid #F3F6FB;
    padding-bottom: 12px;
    font-size: 17px;
    font-weight: 700;
}
.product-description {
    padding-top: 16px;
    color: #0B2A5B;
    font-size: 13px;
    line-height: 1.8;
}
.product-description :deep(ul),
.product-description :deep(ol) {
    padding-left: 22px;
    list-style: disc;
}
.product-description :deep(a) {
    color: #E30613;
    text-decoration: underline;
}
.image-lightbox {
    position: fixed;
    z-index: 1000;
    inset: 0;
    display: grid;
    grid-template-columns: 64px minmax(0, 1fr) 64px;
    grid-template-rows: minmax(0, 1fr) auto;
    align-items: center;
    justify-items: center;
    gap: 18px;
    padding: 28px;
    background: #0B2A5Bf2;
    color: white;
    outline: none;
}
.lightbox-image-wrap {
    grid-column: 2;
    grid-row: 1;
    width: min(100%, 1100px);
    height: min(78vh, 900px);
    overflow: hidden;
    cursor: zoom-in;
}
.lightbox-image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 220ms ease;
}
.lightbox-image-wrap:hover img {
    transform: scale(1.7);
}
.lightbox-close,
.lightbox-arrow {
    display: grid;
    place-items: center;
    border: 1px solid #ffffff38;
    border-radius: 50%;
    background: #ffffff16;
    color: #fff;
    transition:
        background-color 160ms ease,
        transform 160ms ease;
}
.lightbox-close:hover,
.lightbox-arrow:hover {
    transform: scale(1.06);
    background: #ffffff2e;
}
.lightbox-close {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 44px;
    height: 44px;
}
.lightbox-arrow {
    z-index: 1;
    width: 48px;
    height: 48px;
}
.lightbox-arrow.previous {
    grid-column: 1;
    grid-row: 1;
}
.lightbox-arrow.next {
    grid-column: 3;
    grid-row: 1;
}
.lightbox-caption {
    grid-column: 2;
    grid-row: 2;
    display: flex;
    width: min(100%, 1100px);
    justify-content: space-between;
    gap: 18px;
    color: #ffffffc9;
    font-size: 12px;
}
@media (max-width: 750px) {
    .product-detail-grid {
        grid-template-columns: 1fr;
        gap: 27px;
    }
    .main-image {
        min-height: 0;
        aspect-ratio: 1 / 1.04;
    }
    .product-info {
        padding: 0 1px;
    }
    .product-detail-page {
        padding: 21px 18px 55px;
    }
    .image-lightbox {
        grid-template-columns: 44px minmax(0, 1fr) 44px;
        gap: 8px;
        padding: 18px 9px;
    }
    .lightbox-image-wrap {
        height: 74vh;
    }
    .lightbox-arrow {
        width: 40px;
        height: 40px;
    }
    .lightbox-close {
        top: 12px;
        right: 12px;
        width: 40px;
        height: 40px;
    }
    .zoom-hint {
        display: none;
    }
}
@media (max-width: 420px) {
    .product-detail-page {
        padding-inline: 13px;
    }
    .product-detail-grid {
        gap: 22px;
    }
    .main-image {
        aspect-ratio: 1 / 1.08;
    }
    .action-row {
        grid-template-columns: 1fr;
    }
    .image-lightbox {
        grid-template-columns: 36px minmax(0, 1fr) 36px;
        gap: 5px;
        padding-inline: 5px;
    }
    .lightbox-arrow {
        width: 34px;
        height: 34px;
    }
}
@media (prefers-reduced-motion: reduce) {
    .main-image img,
    .lightbox-image-wrap img,
    .lightbox-close,
    .lightbox-arrow {
        transition: none;
    }
}
</style>
