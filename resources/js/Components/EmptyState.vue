<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="border border-dashed border-slate-300 bg-white px-8 py-16 text-center">
        <h2 class="text-lg font-semibold text-slate-900">{{ title }}</h2>
        <p v-if="description" class="mx-auto mt-2 max-w-md text-sm text-slate-600">{{ description }}</p>
        <div class="mt-6">
            <slot />
        </div>
    </div>
</template>
