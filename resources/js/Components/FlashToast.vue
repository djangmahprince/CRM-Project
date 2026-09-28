<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const visible = ref(false);

const message = computed(() => page.props.flash?.success || page.props.flash?.status || '');

watch(message, (value) => {
    visible.value = Boolean(value);
    if (value) {
        window.setTimeout(() => {
            visible.value = false;
        }, 4000);
    }
}, { immediate: true });
</script>

<template>
    <div
        v-if="visible && message"
        class="fixed bottom-6 right-6 z-50 max-w-sm border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900 shadow-sm"
        role="status"
    >
        {{ message }}
    </div>
</template>
