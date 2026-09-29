<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    error: {
        type: String,
        default: '',
    },
    forId: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div>
        <label :for="forId" class="block text-sm font-medium text-slate-700">{{ label }}</label>
        <div class="mt-2">
            <slot />
        </div>
        <p v-if="error" class="mt-2 text-sm text-danger">{{ error }}</p>
    </div>
</template>
