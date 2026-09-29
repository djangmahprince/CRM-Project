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
    <section class="ns-card p-6">
        <div class="mb-6 border-b border-slate-100 pb-4">
            <h2 class="text-lg font-semibold text-slate-950">{{ title }}</h2>
            <p v-if="description" class="mt-1 text-sm text-slate-600">{{ description }}</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <slot />
        </div>
    </section>
</template>
