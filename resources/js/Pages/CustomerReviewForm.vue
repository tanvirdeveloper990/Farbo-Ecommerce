<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Star, Save, ChevronLeft, ImagePlus } from "@lucide/vue";
import AdminLayout from "../Components/AdminLayout.vue";
import Field from "../Components/Field.vue";
import MediaPicker from "../Components/MediaPicker.vue";
const props = defineProps({ review: Object });
const form = useForm({
    name: props.review?.name || "",
    image_path: "",
    remove_image: false,
    star: props.review?.star || 5,
    description: props.review?.description || "",
    status: props.review?.status ?? false,
});
function save() {
    form.post(
        "/admin/customer-reviews" + (props.review ? "/" + props.review.id : ""),
    );
}
</script>
<template>
    <Head
        :title="review ? 'Edit customer review' : 'Create customer review'"
    /><AdminLayout title="Customer reviews"
        ><Link href="/admin/customer-reviews" class="back-link"
            ><ChevronLeft :size="16" />All reviews</Link
        >
        <div class="page-heading">
            <div>
                <span class="eyebrow">CUSTOMER STORIES</span>
                <h1>{{ review ? "Edit review" : "Create review" }}</h1>
                <p>A customer's experience, in their own words.</p>
            </div>
        </div>
        <form @submit.prevent="save" class="review-form">
            <section class="panel form-panel">
                <div class="review-grid">
                    <div>
                        <Field
                            id="review-name"
                            label="Customer name"
                            :error="form.errors.name"
                            ><input
                                id="review-name"
                                v-model="form.name"
                                required
                                maxlength="150"
                                placeholder="Customer's name"
                        /></Field>
                        <fieldset class="rating-field">
                            <legend>Star rating</legend>
                            <div
                                class="rating-options"
                                role="radiogroup"
                                aria-label="Star rating"
                            >
                                <label
                                    v-for="n in 5"
                                    :key="n"
                                    :class="{ chosen: form.star >= n }"
                                    ><input
                                        v-model="form.star"
                                        type="radio"
                                        :value="n"
                                        name="star"
                                        :aria-label="n + ' stars'" /><Star
                                        :size="27"
                                        :fill="
                                            form.star >= n
                                                ? 'currentColor'
                                                : 'none'
                                        " /></label
                                ><span>{{ form.star }} / 5</span>
                            </div>
                            <p v-if="form.errors.star" class="field-error">
                                {{ form.errors.star }}
                            </p>
                        </fieldset>
                        <Field
                            id="review-description"
                            label="Description"
                            :error="form.errors.description"
                            hint="Use the customer's original feedback."
                        >
                            <textarea
                                id="review-description"
                                v-model="form.description"
                                required
                                rows="7"
                                maxlength="3000"
                                placeholder="Share their experience…"
                            ></textarea></Field
                        ><small class="character-count"
                            >{{ form.description.length }} / 3000</small
                        >
                    </div>
                    <aside>
                        <div class="portrait">
                            <img
                                v-if="
                                    !form.remove_image &&
                                    (form.image_path || review?.image)
                                "
                                :src="form.image_path || review.image"
                                alt="Customer image preview"
                            />
                            <div v-else>
                                <ImagePlus :size="32" /><span
                                    >Customer photo</span
                                >
                            </div>
                        </div>
                        <Field
                            id="review-image"
                            label="Image"
                            :error="form.errors.image || form.errors.image_path"
                            hint="Optional. Choose from the media library; upload new files from Media."
                        >
                            <MediaPicker
                                id="review-image"
                                v-model="form.image_path"
                                file-input
                                label="Choose customer image"
                                @update:model-value="form.remove_image = false"
                            />
                        </Field>
                        <label
                            v-if="review?.image && !form.image_path"
                            class="remove-image"
                            ><input
                                v-model="form.remove_image"
                                type="checkbox"
                            />Remove current image</label
                        ><Field
                            id="review-status"
                            label="Status"
                            :error="form.errors.status"
                            ><select id="review-status" v-model="form.status">
                                <option :value="false">
                                    Inactive — hidden from storefront
                                </option>
                                <option :value="true">
                                    Active — visible on storefront
                                </option>
                            </select></Field
                        >
                        <p class="status-note">
                            Only active reviews appear on your website.
                        </p>
                    </aside>
                </div>
                <div class="review-save">
                    <button class="btn primary" :disabled="form.processing">
                        <Save :size="18" />{{
                            form.processing ? "Saving…" : "Save review"
                        }}
                    </button>
                </div>
            </section>
        </form></AdminLayout
    >
</template>
<style scoped>
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #6f8275;
    margin-bottom: 20px;
}
.review-form {
    max-width: 1100px;
}
.review-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 40px;
}
.rating-field {
    border: 0;
    padding: 0;
    margin: 24px 0;
}
.rating-field legend {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 12px;
}
.rating-options {
    display: flex;
    align-items: center;
    gap: 10px;
}
.rating-options label {
    position: relative;
    cursor: pointer;
    color: #c9d0c9;
}
.rating-options label.chosen {
    color: #b88735;
}
.rating-options input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    margin: 0;
}
.rating-options label:focus-within {
    outline: 2px solid #216849;
    outline-offset: 4px;
    border-radius: 4px;
}
.rating-options span {
    color: #859185;
    font-size: 12px;
    margin-left: 10px;
}
.portrait {
    height: 205px;
    background: #f3f6ef;
    border: 1px dashed #cfdcca;
    border-radius: 15px;
    display: grid;
    place-items: center;
    margin-bottom: 18px;
    overflow: hidden;
}
.portrait img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}
.portrait > div {
    display: grid;
    justify-items: center;
    gap: 12px;
    color: #8b9a86;
    font-size: 13px;
}
.review-form textarea {
    width: 100%;
    resize: vertical;
}
.character-count {
    display: block;
    text-align: right;
    color: #8a958b;
}
.remove-image {
    display: flex;
    gap: 7px;
    align-items: center;
    font-size: 12px;
    margin-bottom: 20px;
}
.remove-image input {
    width: auto;
}
.status-note {
    font-size: 12px;
    color: #8a958b;
    line-height: 1.6;
}
.review-save {
    display: flex;
    justify-content: flex-end;
    border-top: 1px solid #edf0e9;
    padding-top: 24px;
    margin-top: 26px;
}
@media (max-width: 760px) {
    .review-grid {
        grid-template-columns: 1fr;
        gap: 25px;
    }
}
</style>
