<script setup>
import { computed, ref, watch } from "vue";
import { Check, FileImage, LoaderCircle, Search, Upload, X } from "@lucide/vue";

const props = defineProps({
    id: { type: String, default: undefined },
    modelValue: { type: [String, Array], default: "" },
    multiple: { type: Boolean, default: false },
    label: { type: String, default: "Choose from media library" },
    fileInput: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    maxSelections: { type: Number, default: Number.POSITIVE_INFINITY },
});
const emit = defineEmits(["update:modelValue"]);
const open = ref(false);
const search = ref("");
const assets = ref([]);
const page = ref(1);
const lastPage = ref(1);
const loading = ref(false);
const uploading = ref(false);
const error = ref("");
const selected = ref([]);
const uploadInput = ref(null);
let searchTimer;
const selectedCount = computed(() => selected.value.length);
const currentSelectionCount = computed(() =>
    Array.isArray(props.modelValue)
        ? props.modelValue.length
        : props.modelValue
          ? 1
          : 0,
);

watch(search, () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => loadAssets(1), 250);
});

async function loadAssets(nextPage = 1) {
    loading.value = true;
    error.value = "";
    try {
        const params = new URLSearchParams({
            page: String(nextPage),
            search: search.value,
        });
        const response = await fetch(`/admin/media/library?${params}`, {
            headers: { Accept: "application/json" },
            credentials: "same-origin",
        });
        if (!response.ok)
            throw new Error("Could not load files from the media library.");
        const result = await response.json();
        assets.value =
            nextPage === 1 ? result.data : [...assets.value, ...result.data];
        page.value = result.current_page;
        lastPage.value = result.last_page;
    } catch (exception) {
        error.value = exception.message || "Could not load media files.";
    } finally {
        loading.value = false;
    }
}

function xsrfToken() {
    const cookie = document.cookie
        .split("; ")
        .find((item) => item.startsWith("XSRF-TOKEN="));

    return cookie ? decodeURIComponent(cookie.slice("XSRF-TOKEN=".length)) : "";
}

async function uploadFiles(event) {
    const files = [...(event.target.files || [])];
    event.target.value = "";
    if (!files.length) return;

    uploading.value = true;
    error.value = "";
    try {
        for (const file of files) {
            const body = new FormData();
            body.append("file", file);
            const headers = { Accept: "application/json" };
            const token = xsrfToken();
            if (token) headers["X-XSRF-TOKEN"] = token;

            const response = await fetch("/admin/media", {
                method: "POST",
                body,
                headers,
                credentials: "same-origin",
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(
                    result.errors?.file?.[0] ||
                        result.message ||
                        "The image could not be uploaded. Check its format and size.",
                );
            }

            const asset = result.data;
            assets.value = [
                asset,
                ...assets.value.filter((item) => item.id !== asset.id),
            ];
        }
    } catch (exception) {
        error.value = exception.message || "The image could not be uploaded.";
    } finally {
        uploading.value = false;
    }
}

function show() {
    selected.value = props.multiple
        ? [...(Array.isArray(props.modelValue) ? props.modelValue : [])]
        : props.modelValue
          ? [props.modelValue]
          : [];
    search.value = "";
    open.value = true;
    loadAssets(1);
}

function toggle(asset) {
    if (!asset.is_image) return;
    if (!props.multiple) {
        error.value = "";
        selected.value = selected.value.includes(asset.url) ? [] : [asset.url];
        return;
    }
    if (
        !selected.value.includes(asset.url) &&
        selected.value.length >= props.maxSelections
    ) {
        error.value = `You can select up to ${props.maxSelections} images.`;
        return;
    }
    error.value = "";
    selected.value = selected.value.includes(asset.url)
        ? selected.value.filter((url) => url !== asset.url)
        : [...selected.value, asset.url];
}

function clearSelection() {
    selected.value = [];
    emit("update:modelValue", props.multiple ? [] : "");
}

function confirmSelection() {
    emit(
        "update:modelValue",
        props.multiple ? selected.value : selected.value[0] || "",
    );
    open.value = false;
}
</script>

<template>
    <div
        class="media-picker-control"
        :class="{ 'media-picker-file-control': fileInput }"
    >
        <button
            type="button"
            :id="id"
            :disabled="disabled"
            class="btn secondary media-picker-trigger"
            :class="{ 'media-picker-file-trigger': fileInput }"
            @click="show"
        >
            <Upload v-if="fileInput" :size="16" />
            <FileImage v-else :size="16" />
            <span>{{
                fileInput
                    ? currentSelectionCount
                        ? multiple
                            ? `${currentSelectionCount} images selected`
                            : "Image selected"
                        : label
                    : label
            }}</span>
        </button>
        <button
            v-if="fileInput && currentSelectionCount"
            type="button"
            class="media-picker-clear"
            :aria-label="
                multiple ? 'Clear selected images' : 'Clear selected image'
            "
            title="Clear selection"
            @click.stop="clearSelection"
        >
            <X :size="16" />
        </button>
        <div
            v-if="open"
            class="media-picker-overlay"
            @click.self="open = false"
            @keydown.esc="open = false"
        >
            <section
                class="media-picker-dialog"
                role="dialog"
                aria-modal="true"
                aria-label="Choose media"
            >
                <header class="media-picker-heading">
                    <div>
                        <span class="eyebrow">MEDIA LIBRARY</span>
                        <h2>Select {{ multiple ? "images" : "an image" }}</h2>
                    </div>
                    <button
                        type="button"
                        class="icon-button"
                        aria-label="Close media library"
                        @click="open = false"
                    >
                        <X :size="18" />
                    </button>
                </header>
                <label class="admin-search media-picker-search">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search images"
                    />
                </label>
                <div class="media-picker-upload-row">
                    <input
                        ref="uploadInput"
                        class="media-picker-upload-input"
                        type="file"
                        accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                        :multiple="multiple"
                        :disabled="uploading || loading"
                        @change="uploadFiles"
                    />
                    <button
                        type="button"
                        class="btn secondary"
                        :disabled="uploading || loading"
                        @click="uploadInput?.click()"
                    >
                        <LoaderCircle
                            v-if="uploading"
                            class="media-picker-spinner"
                            :size="16"
                        />
                        <Upload v-else :size="16" />
                        {{ uploading ? "Uploading…" : "Upload to library" }}
                    </button>
                    <span>Images up to 20 MB</span>
                </div>
                <p v-if="error" class="media-picker-error" role="alert">
                    {{ error }}
                </p>
                <div class="media-picker-grid">
                    <button
                        v-for="asset in assets"
                        :key="asset.id"
                        type="button"
                        class="media-picker-item"
                        :class="{
                            selected: selected.includes(asset.url),
                            disabled: !asset.is_image,
                        }"
                        :disabled="!asset.is_image"
                        @click="toggle(asset)"
                    >
                        <img
                            v-if="asset.is_image"
                            :src="asset.url"
                            :alt="asset.name"
                            loading="lazy"
                        />
                        <span v-else
                            ><FileImage :size="28" /> Image files only</span
                        >
                        <span class="media-picker-name">{{ asset.name }}</span>
                        <Check
                            v-if="selected.includes(asset.url)"
                            class="media-picker-check"
                            :size="19"
                        />
                    </button>
                    <div
                        v-if="!assets.length && !loading && !error"
                        class="media-picker-empty"
                    >
                        No images found. Upload one here to add it to the
                        library.
                    </div>
                </div>
                <div class="media-picker-footer">
                    <button
                        v-if="page < lastPage"
                        type="button"
                        class="btn secondary"
                        :disabled="loading"
                        @click="loadAssets(page + 1)"
                    >
                        {{ loading ? "Loading…" : "Load more" }}
                    </button>
                    <span v-else-if="loading" class="media-picker-loading">
                        <LoaderCircle :size="15" /> Loading…
                    </span>
                    <span v-if="multiple">{{ selectedCount }} selected</span>
                    <div>
                        <button
                            type="button"
                            class="btn secondary"
                            @click="open = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            class="btn primary"
                            :disabled="
                                (!selectedCount && !currentSelectionCount) ||
                                uploading
                            "
                            @click="confirmSelection"
                        >
                            {{
                                !selectedCount && currentSelectionCount
                                    ? "Clear selection"
                                    : multiple
                                      ? "Add selected images"
                                      : "Use selected image"
                            }}
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.media-picker-control {
    margin-top: 9px;
}
.media-picker-file-control {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 0;
}
.media-picker-trigger {
    min-height: 38px;
    font-size: 11px;
}
.media-picker-file-trigger {
    display: flex;
    flex: 1;
    width: 100%;
    min-height: 44px;
    justify-content: flex-start;
    gap: 10px;
    padding: 0 12px;
    border: 1px solid #dce5e9;
    border-radius: 9px;
    background: #fff;
    color: #536777;
    font-size: 13px;
    font-weight: 400;
    text-align: left;
}
.media-picker-clear {
    display: grid;
    width: 34px;
    height: 34px;
    flex: 0 0 auto;
    place-items: center;
    border: 1px solid #eadbda;
    border-radius: 8px;
    background: #fff;
    color: #a3463d;
    cursor: pointer;
}
.media-picker-clear:hover {
    border-color: #cf8e86;
    background: #fff7f6;
}
.media-picker-file-trigger:hover {
    border-color: #6fa997;
    background: #f8fbfa;
}
.media-picker-file-trigger svg {
    flex: 0 0 auto;
    color: #16876f;
}
.media-picker-overlay {
    position: fixed;
    inset: 0;
    z-index: 100;
    display: grid;
    place-items: center;
    padding: 20px;
    background: #101820a8;
}
.media-picker-dialog {
    display: flex;
    width: min(860px, 100%);
    max-height: min(780px, 90vh);
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #e1e8ed;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 24px 70px #07111f45;
}
.media-picker-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 23px;
    border-bottom: 1px solid #e9eef1;
}
.media-picker-heading h2 {
    margin-top: 4px;
    color: #203548;
    font-size: 20px;
}
.media-picker-search {
    flex: none;
    margin: 16px 20px 0;
}
.media-picker-upload-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 20px 14px;
}
.media-picker-upload-row > span {
    color: #7c8b96;
    font-size: 11px;
}
.media-picker-upload-input {
    display: none;
}
.media-picker-spinner {
    animation: media-picker-spin 0.9s linear infinite;
}
@keyframes media-picker-spin {
    to {
        transform: rotate(360deg);
    }
}
.media-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(132px, 1fr));
    gap: 11px;
    min-height: 150px;
    overflow-y: auto;
    padding: 17px 20px;
}
.media-picker-item {
    position: relative;
    min-width: 0;
    overflow: hidden;
    border: 1px solid #e0e7eb;
    border-radius: 10px;
    background: #f8fafb;
    padding: 0;
    text-align: left;
}
.media-picker-item.selected {
    border: 2px solid #16876f;
    box-shadow: 0 0 0 3px #16876f1c;
}
.media-picker-item.disabled {
    cursor: not-allowed;
    opacity: 0.55;
}
.media-picker-item img,
.media-picker-item > span:first-of-type {
    display: grid;
    width: 100%;
    aspect-ratio: 1.3;
    place-items: center;
    object-fit: contain;
    color: #8997a3;
    font-size: 10px;
}
.media-picker-name {
    display: block;
    overflow: hidden;
    padding: 8px 9px;
    color: #405467;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.media-picker-check {
    position: absolute;
    top: 7px;
    right: 7px;
    border-radius: 50%;
    background: #16876f;
    padding: 2px;
    color: #fff;
}
.media-picker-empty {
    grid-column: 1 / -1;
    align-self: center;
    padding: 30px;
    color: #83919d;
    font-size: 13px;
    text-align: center;
}
.media-picker-error {
    padding: 12px 20px 0;
    color: #b42318;
}
.media-picker-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-top: 1px solid #e9eef1;
    padding: 14px 20px;
    color: #768796;
    font-size: 11px;
}
.media-picker-footer > div {
    display: flex;
    gap: 8px;
}
.media-picker-loading {
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
@media (max-width: 560px) {
    .media-picker-overlay {
        padding: 10px;
    }
    .media-picker-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 7px;
        padding: 12px;
    }
    .media-picker-upload-row {
        flex-wrap: wrap;
        padding-inline: 12px;
    }
}
</style>
