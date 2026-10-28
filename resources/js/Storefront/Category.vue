<script setup>
import { Head, Link, router } from "@inertiajs/vue3";
import StorefrontLayout from "./StorefrontLayout.vue";
import ProductCard from "./Components/ProductCard.vue";
import { createStorefrontContext } from "./storefrontContext";
import { provide, reactive, watch } from "vue";

const props = defineProps({
    category: Object,
    allCategories: Boolean,
    products: Object,
    selectedSubcategory: [String, Number, null],
    selectedChildCategory: [String, Number, null],
    filters: Object,
    filterOptions: Object,
    priceRange: Object,
});
const storefront = createStorefrontContext();
provide("storefront", storefront);
const { taka, toggleWish, wishlist, openQuickView } = storefront;
const form = reactive({
    sort: "",
    min_price: "",
    max_price: "",
    brands: [],
    colors: [],
    sizes: [],
    subcategory: "",
    child_category: "",
});
watch(
    () => [
        props.filters,
        props.selectedSubcategory,
        props.selectedChildCategory,
    ],
    () => {
        Object.assign(form, {
            ...props.filters,
            brands: [...(props.filters?.brands || [])],
            colors: [...(props.filters?.colors || [])],
            sizes: [...(props.filters?.sizes || [])],
            subcategory: props.selectedSubcategory
                ? String(props.selectedSubcategory)
                : "",
            child_category: props.selectedChildCategory
                ? String(props.selectedChildCategory)
                : "",
        });
    },
    { immediate: true },
);
function applyFilters() {
    const query = {};
    for (const key of [
        "sort",
        "min_price",
        "max_price",
        "subcategory",
        "child_category",
    ]) {
        if (form[key] !== "") query[key] = form[key];
    }
    for (const key of ["brands", "colors", "sizes"]) {
        if (form[key].length) query[key] = form[key];
    }
    router.get(props.allCategories ? "/categories" : `/categories/${encodeURIComponent(props.category.slug || props.category.id)}`, query, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}
function selectSubcategory(id = "") {
    form.subcategory = id ? String(id) : "";
    form.child_category = "";
    applyFilters();
}
function selectChild(subcategoryId, childId) {
    form.subcategory = String(subcategoryId);
    form.child_category = String(childId);
    applyFilters();
}
function clearFilters() {
    Object.assign(form, {
        sort: "",
        min_price: "",
        max_price: "",
        brands: [],
        colors: [],
        sizes: [],
        subcategory: "",
        child_category: "",
    });
    applyFilters();
}
</script>

<template>
    <Head :title="`${category.name} | Collection`" />
    <StorefrontLayout>
        <section class="category-hero">
            <img
                v-if="category.image"
                :src="category.image"
                :alt="category.name"
            />
            <div class="category-hero-overlay"></div>
            <div class="category-hero-copy">
                <Link href="/" class="breadcrumb">Home / Categories /</Link>
                <h1>{{ category.name }}</h1>
                <p>{{ allCategories ? "Explore products from all our collections." : `Explore our ${category.name} collection.` }}</p>
            </div>
        </section>

        <main class="category-page">
            <aside class="filter-sidebar">
                <div class="filter-title">
                    <h2>Filters</h2>
                    <button type="button" @click="clearFilters">
                        Clear all
                    </button>
                </div>
                <section class="filter-section">
                    <label class="filter-label" for="category-sort"
                        >Sort by</label
                    >
                    <select id="category-sort" v-model="form.sort">
                        <option value="">Latest</option>
                        <option value="price_asc">Price: Low to high</option>
                        <option value="price_desc">Price: High to low</option>
                    </select>
                </section>
                <section class="filter-section">
                    <h3>Price range</h3>
                    <div class="price-inputs">
                        <label>
                            <span>Min</span>
                            <input
                                v-model="form.min_price"
                                type="number"
                                min="0"
                                :max="priceRange.maximum"
                                placeholder="0"
                            />
                        </label>
                        <label>
                            <span>Max</span>
                            <input
                                v-model="form.max_price"
                                type="number"
                                min="0"
                                :max="priceRange.maximum"
                                :placeholder="String(priceRange.maximum || 0)"
                            />
                        </label>
                    </div>
                </section>
                <section class="filter-section">
                    <h3>Category</h3>
                    <button
                        type="button"
                        class="category-option"
                        :class="{ selected: !form.subcategory }"
                        @click="selectSubcategory()"
                    >
                        All {{ category.name }}
                    </button>
                    <div
                        v-for="subcategory in category.subcategories"
                        :key="subcategory.id"
                        class="category-option-group"
                    >
                        <button
                            type="button"
                            class="category-option"
                            :class="{
                                selected:
                                    form.subcategory ===
                                        String(subcategory.id) &&
                                    !form.child_category,
                            }"
                            @click="selectSubcategory(subcategory.id)"
                        >
                            {{ subcategory.name }}
                        </button>
                        <button
                            v-for="child in subcategory.children"
                            :key="child.id"
                            type="button"
                            class="category-option child-option"
                            :class="{
                                selected:
                                    form.child_category === String(child.id),
                            }"
                            @click="selectChild(subcategory.id, child.id)"
                        >
                            {{ child.name }}
                        </button>
                    </div>
                </section>
                <section
                    v-for="(options, key) in {
                        brands: filterOptions.brands,
                        colors: filterOptions.colors,
                        sizes: filterOptions.sizes,
                    }"
                    :key="key"
                    class="filter-section"
                >
                    <h3>{{ key[0].toUpperCase() + key.slice(1) }}</h3>
                    <label
                        v-for="option in options"
                        :key="option.id"
                        class="filter-checkbox"
                    >
                        <input
                            v-model="form[key]"
                            type="checkbox"
                            :value="String(option.id)"
                        />
                        <span
                            v-if="key === 'colors'"
                            class="color-dot"
                            :style="{ backgroundColor: option.color || '#eee' }"
                        ></span>
                        <span>{{ option.name }}</span>
                    </label>
                    <p v-if="!options.length" class="no-filter-options">
                        No {{ key }} available
                    </p>
                </section>
                <button
                    class="apply-filters"
                    type="button"
                    @click="applyFilters"
                >
                    Apply filters
                </button>
            </aside>

            <section class="category-results">
                <div class="category-toolbar">
                    <div>
                        <h2>
                            {{
                                selectedChildCategory
                                    ? category.subcategories
                                          .flatMap((item) => item.children)
                                          .find(
                                              (item) =>
                                                  item.id ===
                                                  Number(selectedChildCategory),
                                          )?.name
                                    : selectedSubcategory
                                      ? category.subcategories.find(
                                            (item) =>
                                                item.id ===
                                                Number(selectedSubcategory),
                                        )?.name
                                      : category.name
                            }}
                        </h2>
                        <p>{{ products.total }} products</p>
                    </div>
                </div>
                <div v-if="products.data.length" class="category-products">
                    <ProductCard v-for="product in products.data" :key="product.id" :product="product" :wished="wishlist.includes(product.id)" @wish="toggleWish" />
                </div>
                <div v-else class="category-empty">
                    <h2>No matching products</h2>
                    <p>Try changing or clearing your selected filters.</p>
                </div>

                <nav v-if="products.last_page > 1" class="pagination">
                    <Link
                        v-for="(link, index) in products.links"
                        :key="index"
                        :href="link.url || '#'"
                        :class="{ active: link.active, disabled: !link.url }"
                        v-html="link.label"
                    />
                </nav>
            </section>
        </main>
    </StorefrontLayout>
</template>

<style scoped>
.category-hero {
    position: relative;
    display: flex;
    min-height: 230px;
    align-items: flex-end;
    overflow: hidden;
    background: #F3F6FB;
}
.category-hero > img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.category-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #0B2A5Bbd, #0B2A5B24);
}
.category-hero-copy {
    position: relative;
    z-index: 1;
    width: min(1200px, 100%);
    margin: 0 auto;
    padding: 35px 24px;
    color: white;
}
.breadcrumb {
    color: #ffffffb5;
    font-size: 12px;
}
.category-hero h1 {
    margin-top: 9px;
    font-size: clamp(32px, 5vw, 54px);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
}
.category-hero p {
    margin-top: 5px;
    color: #ffffffcc;
    font-size: 14px;
}
.category-page {
    display: grid;
    grid-template-columns: 250px minmax(0, 1fr);
    align-items: start;
    gap: 30px;
    width: min(1320px, 100%);
    min-height: 380px;
    margin: 0 auto;
    padding: 36px 24px 70px;
}
.filter-sidebar {
    position: sticky;
    top: 20px;
    border: 1px solid #dce4ef;
    border-radius: 10px;
    background: #fff;
    padding: 18px;
}
.filter-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #dce4ef;
    padding-bottom: 14px;
}
.filter-title h2 {
    font-size: 17px;
    font-weight: 700;
}
.filter-title button {
    color: #E30613;
    font-size: 11px;
}
.filter-section {
    border-bottom: 1px solid #dce4ef;
    padding: 16px 0;
}
.filter-section h3,
.filter-label {
    display: block;
    margin-bottom: 10px;
    color: #0B2A5B;
    font-size: 12px;
    font-weight: 700;
}
.filter-section select,
.price-inputs input {
    width: 100%;
    border: 1px solid #DCE4EF;
    border-radius: 5px;
    background: #fff;
    padding: 9px 10px;
    color: #0B2A5B;
    font-size: 12px;
}
.price-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.price-inputs label span {
    display: block;
    margin-bottom: 4px;
    color: #777;
    font-size: 10px;
}
.price-inputs input {
    padding: 8px;
}
.filter-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 9px;
    color: #0B2A5B;
    font-size: 12px;
    cursor: pointer;
}
.filter-checkbox input {
    accent-color: #E30613;
}
.color-dot {
    width: 13px;
    height: 13px;
    border: 1px solid #DCE4EF;
    border-radius: 50%;
}
.no-filter-options {
    color: #64748B;
    font-size: 11px;
}
.category-option-group {
    display: grid;
}
.category-option {
    width: 100%;
    padding: 5px 0;
    color: #64748B;
    font-size: 12px;
    text-align: left;
}
.category-option:hover,
.category-option.selected {
    color: #E30613;
    font-weight: 650;
}
.child-option {
    padding-left: 15px;
    color: #64748B;
    font-size: 11px;
}
.apply-filters {
    width: 100%;
    margin-top: 16px;
    border-radius: 5px;
    background: #E30613;
    padding: 11px;
    color: #fff;
    font-size: 12px;
    font-weight: 650;
}
.category-results {
    min-width: 0;
}
.category-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 25px;
    border-bottom: 1px solid #DCE4EF;
    padding-bottom: 20px;
}
.category-toolbar h2 {
    font-size: 21px;
    font-weight: 650;
}
.category-toolbar p {
    margin-top: 4px;
    color: #777;
    font-size: 12px;
}
.category-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
.category-filters button {
    border: 1px solid #DCE4EF;
    border-radius: 999px;
    padding: 8px 13px;
    color: #0B2A5B;
    font-size: 11px;
}
.category-filters button.active {
    border-color: #E30613;
    background: #E30613;
    color: white;
}
.category-products {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 28px 16px;
}
.prod-item {
    min-width: 0;
}
.product-visual {
    position: relative;
    aspect-ratio: 3 / 4;
    overflow: hidden;
    background: #f5f5f5;
}
.product-image {
    position: absolute;
    inset: 0;
    display: grid;
    width: 100%;
    height: 100%;
    place-items: center;
    overflow: hidden;
    color: #737373;
    font-size: 12px;
}
.product-image img {
    display: block;
    max-width: 100%;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: top;
    transition: transform 0.7s ease-out;
}
.product-visual:hover .product-image img {
    display: block;
    max-width: 100%;
    transform: scale(1.05);
}
.wish-btn {
    position: absolute;
    top: 8px;
    left: 8px;
    display: grid;
    width: 36px;
    height: 36px;
    place-items: center;
    border-radius: 50%;
    color: #111;
    transition: background-color 0.2s;
}
.wish-btn:hover {
    background: #ffffffcc;
}
.wish-btn svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    stroke-width: 1.6;
}
.new-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #fff;
    padding: 5px 9px;
    color: #111;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.15em;
}
.quick-view {
    position: absolute;
    right: 12px;
    bottom: 12px;
    left: 12px;
    display: none;
    transform: translateY(12px);
    background: #111;
    padding: 12px;
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    opacity: 0;
    transition: all 0.3s;
}
.product-visual:hover .quick-view {
    transform: translateY(0);
    opacity: 1;
}
.quick-view:hover {
    background: #333;
}
.quick-view-mobile {
    position: absolute;
    right: 8px;
    bottom: 8px;
    display: grid;
    width: 36px;
    height: 36px;
    place-items: center;
    border-radius: 50%;
    background: #fff;
    color: #111;
    box-shadow: 0 2px 8px #0002;
}
.quick-view-mobile svg {
    width: 17px;
    height: 17px;
    fill: none;
    stroke: currentColor;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-width: 1.8;
}
.product-copy {
    padding-top: 12px;
}
.product-category {
    overflow: hidden;
    color: #737373;
    font-size: 10px;
    font-weight: 500;
    letter-spacing: 0.2em;
    text-overflow: ellipsis;
    text-transform: uppercase;
    white-space: nowrap;
}
.product-name {
    display: block;
    overflow: hidden;
    margin-top: 4px;
    color: #111;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-overflow: ellipsis;
    text-transform: uppercase;
    white-space: nowrap;
}
.product-name:hover {
    text-decoration: underline;
}
.product-prices {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 6px;
}
.product-prices strong {
    color: #111;
    font-size: 14px;
}
.product-prices span {
    color: #a3a3a3;
    font-size: 12px;
    text-decoration: line-through;
}
.category-empty {
    padding: 90px 15px;
    text-align: center;
}
.category-empty p {
    margin-top: 8px;
    color: #777;
    font-size: 13px;
}
.continue-link {
    display: inline-block;
    margin-top: 18px;
    border-radius: 999px;
    background: #E30613;
    padding: 11px 18px;
    color: white;
    font-size: 12px;
}
.pagination {
    display: flex;
    justify-content: center;
    gap: 6px;
    margin-top: 35px;
}
.pagination :deep(a) {
    border: 1px solid #DCE4EF;
    padding: 7px 11px;
    font-size: 12px;
}
.pagination :deep(a.active) {
    background: #E30613;
    color: white;
}
.pagination :deep(a.disabled) {
    pointer-events: none;
    opacity: 0.4;
}
@media (max-width: 900px) {
    .category-page {
        grid-template-columns: minmax(0, 1fr);
        gap: 20px;
        padding: 24px 16px 50px;
    }
    .filter-sidebar {
        position: static;
    }
}
@media (max-width: 760px) {
    .category-products {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 12px;
    }
    .category-hero {
        min-height: 190px;
    }
}
@media (min-width: 1024px) {
    .quick-view {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .quick-view-mobile {
        display: none;
    }
}
@media (min-width: 1200px) {
    .category-products {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
/* Display the entire photo inside the fixed product card, including on hover. */
.product-image img { object-fit: fill; object-position: center; }
.product-visual:hover .product-image img { transform: none; }
</style>
