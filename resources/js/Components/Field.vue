<script setup>
defineProps({ label: String, error: String, id: String, hint: String });
</script>
<template>
    <div class="field">
        <label :for="id">{{ label }}</label
        ><slot /><small
            v-if="error"
            :id="id + '-error'"
            class="field-error"
            role="alert"
            >{{ error }}</small
        ><small v-else-if="hint" class="field-hint">{{ hint }}</small>
    </div>
</template>
