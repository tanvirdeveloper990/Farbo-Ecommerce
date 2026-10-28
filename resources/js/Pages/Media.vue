<script setup>
import { ref, watch } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import {
    File,
    FileImage,
    FileText,
    HardDrive,
    Search,
    Trash2,
    Upload,
    X,
} from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";

const props = defineProps({ assets: Object, filters: Object });
const search = ref(props.filters.search || "");
const type = ref(props.filters.type || "all");
const form = useForm({ file: null });
const fileInput = ref(null);
const importing = ref(false);
function importWebsiteImages() {
    importing.value = true;
    router.post('/admin/media/import-storefront', {}, {preserveScroll: true, onFinish: () => { importing.value = false; }});
}

watch(
    () => [props.filters.search, props.filters.type],
    ([nextSearch, nextType]) => {
        search.value = nextSearch || "";
        type.value = nextType || "all";
    },
);

function filter() {
    router.get(
        "/admin/media",
        { search: search.value || undefined, type: type.value },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function upload(event) {
    form.file = event.target.files?.[0] || null;
    if (!form.file) return;
    form.post("/admin/media", {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = "";
        },
    });
}

async function remove(asset) {
    if (asset.in_use) {
        await Swal.fire({
            title: "File is in use",
            text: "Replace this file in its current location before deleting it.",
            icon: "info",
            confirmButtonColor: "#176b57",
        });
        return;
    }

    const answer = await Swal.fire({
        title: "Delete this file?",
        text: asset.name,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete file",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#b42318",
    });
    if (answer.isConfirmed) {
        router.delete(`/admin/media/${asset.id}`, { preserveScroll: true });
    }
}

function sizeLabel(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
</script>

<template>
    <Head title="Media library" />
    <AdminLayout title="Media">
        <div class="page-heading">
            <div>
                <span class="eyebrow">STORE FILE MANAGER</span>
                <h1>Media library</h1>
                <p>
                    Upload, find and manage your store files in one place.
                    Raster images are resized and optimized as WebP.
                </p>
            </div>
            <button type="button" class="btn" :disabled="importing" @click="importWebsiteImages">{{ importing ? 'Importing images…' : 'Import website images' }}</button>
            <label class="btn primary upload-button">
                <Upload :size="17" />
                {{ form.processing ? "Optimizing…" : "Upload files" }}
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml,.pdf,.txt,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.mp4,.webm"
                    :disabled="form.processing"
                    @change="upload"
                />
            </label>
        </div>

        <div class="media-notice panel">
            <HardDrive :size="18" />
            <span>
                Images become optimized WebP (up to 2560 px). SVG files are
                sanitized; documents keep their original format. Maximum file
                size: 20 MB.
            </span>
        </div>
        <p v-if="form.errors.file" class="field-error media-error">
            {{ form.errors.file }}
        </p>

        <section class="panel media-library">
            <form
                class="admin-filter-bar media-toolbar"
                @submit.prevent="filter"
            >
                <label class="admin-search">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search by file name"
                    />
                </label>
                <select v-model="type" aria-label="Filter media type">
                    <option value="all">All files</option>
                    <option value="images">Images</option>
                    <option value="documents">Documents</option>
                    <option value="other">Other files</option>
                </select>
                <button type="submit" class="btn primary media-filter-button">
                    <Search :size="16" />
                    Filter
                </button>
            </form>

            <div v-if="assets.data.length" class="media-grid">
                <article
                    v-for="asset in assets.data"
                    :key="asset.id"
                    class="media-card"
                >
                    <a
                        class="media-preview"
                        :href="asset.url"
                        target="_blank"
                        rel="noopener"
                        :aria-label="'Open ' + asset.name"
                    >
                        <img
                            v-if="asset.is_image"
                            :src="asset.url"
                            :alt="asset.name"
                            loading="lazy"
                        />
                        <FileText
                            v-else-if="asset.mime_type === 'application/pdf'"
                            :size="38"
                        />
                        <FileImage
                            v-else-if="asset.mime_type.startsWith('image/')"
                            :size="38"
                        />
                        <File v-else :size="38" />
                    </a>
                    <div class="media-card-info">
                        <strong :title="asset.name">{{ asset.name }}</strong>
                        <span>
                            {{ sizeLabel(asset.size_bytes) }}
                            <template v-if="asset.width && asset.height">
                                · {{ asset.width }} × {{ asset.height }}
                            </template>
                        </span>
                        <small v-if="asset.in_use" class="media-used"
                            >In use</small
                        >
                    </div>
                    <button
                        class="icon-button danger media-delete"
                        :disabled="asset.in_use"
                        :aria-label="'Delete ' + asset.name"
                        :title="asset.in_use ? 'File is in use' : 'Delete file'"
                        @click="remove(asset)"
                    >
                        <Trash2 :size="16" />
                    </button>
                </article>
            </div>
            <div v-else class="media-empty">
                <FileImage :size="42" />
                <h3>No files found</h3>
                <p>Upload a file or adjust your search and filters.</p>
            </div>
            <div v-if="assets.links?.length > 3" class="table-footer">
                <span>
                    {{ assets.from || 0 }}–{{ assets.to || 0 }} of
                    {{ assets.total }} files
                </span>
                <div class="pagination">
                    <template
                        v-for="(link, index) in assets.links"
                        :key="index"
                    >
                        <a
                            v-if="link.url"
                            :href="link.url"
                            :class="{ selected: link.active }"
                        >
                            {{
                                index === 0
                                    ? "Previous"
                                    : index === assets.links.length - 1
                                      ? "Next"
                                      : link.label
                            }}
                        </a>
                        <span v-else>
                            {{
                                index === 0
                                    ? "Previous"
                                    : index === assets.links.length - 1
                                      ? "Next"
                                      : link.label
                            }}
                        </span>
                    </template>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.upload-button {
    position: relative;
    overflow: hidden;
}
.upload-button input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    opacity: 0;
}
.media-notice {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 18px;
    padding: 16px 20px;
    color: #506579;
    font-size: 12px;
    line-height: 1.7;
}
.media-notice svg {
    color: #16876f;
}
.media-error {
    margin: -8px 0 16px;
}
.media-toolbar {
    display: grid;
    grid-template-columns: minmax(240px, 1fr) minmax(160px, 210px) auto;
    align-items: center;
    gap: 12px;
    border-bottom: 1px solid #e8edf0;
}
.media-toolbar .admin-search {
    min-width: 0;
}
.media-toolbar > select {
    width: 100%;
}
.media-filter-button {
    min-height: 44px;
    justify-content: center;
    gap: 8px;
    padding-inline: 20px;
}
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 16px;
    padding: 22px;
}
.media-card {
    position: relative;
    min-width: 0;
    overflow: hidden;
    border: 1px solid #e4eaf0;
    border-radius: 12px;
    background: #fff;
    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease;
}
.media-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 22px #22394f12;
}
.media-preview {
    display: grid;
    aspect-ratio: 1.35;
    place-items: center;
    overflow: hidden;
    background:
        linear-gradient(45deg, #eff2f4 25%, transparent 25%),
        linear-gradient(-45deg, #eff2f4 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #eff2f4 75%),
        linear-gradient(-45deg, transparent 75%, #eff2f4 75%);
    background-color: #f8fafb;
    background-position:
        0 0,
        0 8px,
        8px -8px,
        -8px 0;
    background-size: 16px 16px;
    color: #8b9aa8;
}
.media-preview img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.media-card-info {
    display: grid;
    gap: 4px;
    padding: 12px 42px 13px 13px;
}
.media-card-info strong {
    overflow: hidden;
    color: #2c4052;
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.media-card-info span,
.media-card-info small {
    color: #82909c;
    font-size: 10px;
}
.media-card-info .media-used {
    color: #28775d;
    font-weight: 700;
}
.media-delete {
    position: absolute;
    right: 9px;
    bottom: 10px;
}
.media-delete:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
.media-empty {
    padding: 72px 20px;
    color: #8795a2;
    text-align: center;
}
.media-empty svg {
    margin: auto;
}
.media-empty h3 {
    margin: 15px 0 6px;
    color: #34495b;
}
@media (max-width: 620px) {
    .media-toolbar {
        grid-template-columns: minmax(0, 1fr) auto;
        padding: 14px;
    }
    .media-toolbar .admin-search {
        grid-column: 1 / -1;
    }
    .media-toolbar > select {
        min-width: 0;
    }
    .media-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        padding: 14px;
    }
}
</style>
