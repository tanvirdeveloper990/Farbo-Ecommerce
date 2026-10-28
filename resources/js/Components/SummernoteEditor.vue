<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import $ from "jquery";
import "summernote/dist/summernote-lite.js";
import "summernote/dist/summernote-lite.css";
const props = defineProps({
    modelValue: String,
    disabled: Boolean,
    id: String,
    label: { type: String, default: "Policy content" },
    placeholder: { type: String, default: "Write your policy here…" },
});
const emit = defineEmits(["update:modelValue"]);
const editor = ref(null);
let ready = false;
function preventEscape(event) {
    if (event.key === "Escape" && event.target.closest?.(".note-modal")) {
        event.preventDefault();
        event.stopImmediatePropagation();
    }
}
onMounted(() => {
    const initialContent = props.modelValue || "";
    $(editor.value).summernote({
        height: 420,
        minHeight: 280,
        dialogsInBody: false,
        disableDragAndDrop: true,
        placeholder: props.placeholder,
        styleTags: ["p", "h2", "h3", "h4", "blockquote"],
        toolbar: [
            ["style", ["style"]],
            ["font", ["bold", "italic", "underline", "clear"]],
            ["color", ["color"]],
            ["para", ["ul", "ol", "paragraph"]],
            ["table", ["table"]],
            ["insert", ["link", "hr"]],
            ["history", ["undo", "redo"]],
            ["view", ["fullscreen", "codeview"]],
        ],
        callbacks: {
            onChange: (html) => { if (ready) emit("update:modelValue", html); },
            onChangeCodeview: (html) => { if (ready) emit("update:modelValue", html); },
        },
    });
    $(editor.value).summernote("code", initialContent);
    $(editor.value)
        .next(".note-editor")
        .find(".note-editable")
        .attr("aria-label", props.label);
    ready = true;
    if (props.disabled) $(editor.value).summernote("disable");
    document.addEventListener("keydown", preventEscape, true);
});
watch(
    () => props.modelValue,
    (value) => {
        if (ready && $(editor.value).summernote("code") !== (value || ""))
            $(editor.value).summernote("code", value || "");
    },
);
watch(
    () => props.disabled,
    (value) => {
        if (ready) $(editor.value).summernote(value ? "disable" : "enable");
    },
);
onBeforeUnmount(() => {
    document.removeEventListener("keydown", preventEscape, true);
    if (ready) $(editor.value).summernote("destroy");
});
</script>
<template>
    <div class="policy-editor">
        <textarea :id="id" ref="editor" :aria-label="label"></textarea>
    </div>
</template>
<style>
.policy-editor .note-editor {
    border: 1px solid #dce5df;
    border-radius: 12px;
    overflow: hidden;
}
.policy-editor .note-toolbar {
    background: #f5f8f3;
    border-bottom: 1px solid #dce5df;
    padding: 10px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}
.policy-editor .note-editable {
    padding: 24px !important;
    line-height: 1.8;
    background: #fff;
}
.policy-editor .note-btn {
    font-size: 13px;
    padding: 6px 9px;
    color: #43594a;
    border-color: #dce5df;
}
.policy-editor .note-editable ul {
    list-style: disc;
    padding-left: 24px;
}
.policy-editor .note-editable ol {
    list-style: decimal;
    padding-left: 24px;
}
.policy-editor .note-editable h2 {
    font-size: 26px;
}
.policy-editor .note-editable h3 {
    font-size: 22px;
}
.policy-editor .note-editable h4 {
    font-size: 18px;
}
.policy-editor .note-modal {
    z-index: 1100;
}
.note-modal-backdrop {
    z-index: 1090;
}
.policy-editor .note-modal-content {
    border-radius: 14px;
}
.policy-editor .note-modal input[type="checkbox"] {
    width: auto;
}
.policy-editor .note-modal .close {
    float: right;
    font-size: 26px;
    background: none;
    border: 0;
}
.policy-editor .note-dropdown-menu {
    max-width: calc(100vw - 60px);
}
</style>
