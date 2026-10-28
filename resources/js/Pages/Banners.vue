<script setup>
import { ref, nextTick, onBeforeUnmount } from "vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import { Plus, Pencil, Trash2, X, Image } from "@lucide/vue";
import Swal from "sweetalert2";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";
import MediaPicker from "../Components/MediaPicker.vue";
const props = defineProps({ banners: Array });
const form = useForm({ image_path: "", serial: 1 });
const modal = ref(false),
    editing = ref(null),
    preview = ref(""),
    dialog = ref(null);
let previousFocus;
function open(banner = null) {
    previousFocus = document.activeElement;
    form.reset();
    form.clearErrors();
    editing.value = banner?.id || null;
    form.serial =
        banner?.serial ??
        Math.max(0, ...props.banners.map((b) => b.serial)) + 1;
    preview.value = banner?.image || "";
    form.image_path = "";
    modal.value = true;
    document.body.style.overflow = "hidden";
    nextTick(() => dialog.value?.querySelector("button")?.focus());
}
function close() {
    if (form.processing) return;
    modal.value = false;
    document.body.style.overflow = "";
    previousFocus?.focus();
}
function save() {
    form.post("/admin/banners" + (editing.value ? "/" + editing.value : ""), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            preview.value = "";
            close();
        },
    });
}
async function remove(banner) {
    const answer = await Swal.fire({
        title: "Delete this banner?",
        text: "It will be removed from the storefront.",
        icon: "warning",
        showCancelButton: true,
        showCloseButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "No, keep it",
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonColor: "#b42318",
    });
    if (answer.isConfirmed)
        router.delete("/admin/banners/" + banner.id, { preserveScroll: true });
}
function trap(event) {
    if (event.key !== "Tab") return;
    const controls = [...dialog.value.querySelectorAll("button,input")].filter(
        (e) => !e.disabled,
    );
    const first = controls[0],
        last = controls.at(-1);
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}
onBeforeUnmount(() => {
    document.body.style.overflow = "";
});
</script>
<template>
    <Head title="Banners" /><AdminLayout title="Banners">
        <div class="page-heading">
            <div>
                <span class="eyebrow">STOREFRONT CONTENT</span>
                <h1>Banners</h1>
                <p>Lower serial numbers appear first on your homepage.</p>
            </div>
            <button class="btn primary" @click="open()">
                <Plus :size="18" /> Add banner
            </button>
        </div>
        <div v-if="!banners.length" class="panel banner-empty">
            <Image :size="40" />
            <h3>No banners yet</h3>
            <p>Add an image to bring your storefront to life.</p>
        </div>
        <div class="banner-grid">
            <article
                v-for="banner in banners"
                :key="banner.id"
                class="panel banner-card"
            >
                <img :src="banner.image" :alt="'Banner ' + banner.serial" />
                <div class="banner-card-footer">
                    <span class="pill">Serial {{ banner.serial }}</span>
                    <div>
                        <button
                            class="icon-button"
                            :aria-label="'Edit banner ' + banner.serial"
                            @click="open(banner)"
                        >
                            <Pencil :size="18" /></button
                        ><button
                            class="icon-button"
                            :aria-label="'Delete banner ' + banner.serial"
                            @click="remove(banner)"
                        >
                            <Trash2 :size="18" />
                        </button>
                    </div>
                </div>
            </article>
        </div>
        <div v-if="modal" class="modal-overlay" @keydown="trap">
            <section
                ref="dialog"
                class="modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="banner-title"
            >
                <div class="modal-heading">
                    <div>
                        <span class="eyebrow">HOMEPAGE SLIDER</span>
                        <h2 id="banner-title">
                            {{ editing ? "Edit banner" : "Add banner" }}
                        </h2>
                    </div>
                    <button
                        class="icon-button"
                        aria-label="Close banner form"
                        :disabled="form.processing"
                        @click="close"
                    >
                        <X />
                    </button>
                </div>
                <form @submit.prevent="save">
                    <Field
                        id="banner-image"
                        label="Banner image"
                        :error="form.errors.image || form.errors.image_path"
                        hint="Choose an image from the media library. Upload new files from Media. Recommended ratio 2.4:1."
                    >
                        <MediaPicker
                            id="banner-image"
                            v-model="form.image_path"
                            file-input
                            label="Choose banner image"
                            @update:model-value="(url) => (preview = url)"
                        />
                    </Field>
                    <p
                        v-if="!editing && !form.image_path"
                        class="media-required"
                    >
                        Select an image from the media library.
                    </p>
                    <img
                        v-if="preview"
                        :src="preview"
                        class="banner-preview"
                        alt="Banner preview"
                    />
                    <Field
                        id="serial"
                        label="Serial"
                        :error="form.errors.serial"
                        ><input
                            id="serial"
                            v-model="form.serial"
                            type="number"
                            min="0"
                            max="999999"
                            required
                    /></Field>
                    <div class="modal-footer">
                        <button class="btn primary" :disabled="form.processing">
                            {{ form.processing ? "Saving…" : "Save banner" }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </AdminLayout>
</template>
<style scoped>
.banner-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}
.banner-card {
    overflow: hidden;
}
.banner-card > img {
    width: 100%;
    aspect-ratio: 2.4;
    object-fit: cover;
    display: block;
}
.banner-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px;
}
.banner-card-footer > div {
    display: flex;
    gap: 8px;
}
.banner-empty {
    padding: 50px;
    text-align: center;
}
.banner-empty svg {
    margin: auto;
}
.banner-preview {
    width: 100%;
    max-height: 210px;
    object-fit: contain;
    background: #f5f7f6;
    border-radius: 12px;
    margin: 10px 0 20px;
}
.modal form {
    padding: 0 24px 24px;
}
@media (max-width: 700px) {
    .banner-grid {
        grid-template-columns: 1fr;
    }
}
</style>
