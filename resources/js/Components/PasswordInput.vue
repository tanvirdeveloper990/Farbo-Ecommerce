<script setup>
import { ref, watch } from "vue";
import { Eye, EyeOff } from "@lucide/vue";
defineOptions({ inheritAttrs: false });
const props = defineProps({
    modelValue: { type: String, default: "" },
    id: { type: String, required: true },
});
const emit = defineEmits(["update:modelValue"]);
const visible = ref(false);
watch(
    () => props.modelValue,
    (value) => {
        if (!value) visible.value = false;
    },
);
</script>
<template>
    <div class="password-input">
        <input
            v-bind="$attrs"
            :id="id"
            :type="visible ? 'text' : 'password'"
            :value="modelValue"
            @input="emit('update:modelValue', $event.target.value)"
        />
        <button
            type="button"
            :aria-label="visible ? 'Hide password' : 'Show password'"
            :aria-controls="id"
            :aria-pressed="visible"
            @click="visible = !visible"
        >
            <component :is="visible ? EyeOff : Eye" :size="18" />
        </button>
    </div>
</template>
