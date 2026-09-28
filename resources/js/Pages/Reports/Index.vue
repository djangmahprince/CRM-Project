<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    reports: Array,
});
</script>

<template>
    <AppLayout title="Reports">
        <ul class="divide-y divide-slate-100 border border-slate-200 bg-white shadow-sm">
            <li v-for="report in reports" :key="report.key" class="p-6 hover:bg-slate-50">
                <Link :href="`/reports/${report.key}`" class="block">
                    <h2 class="text-lg font-semibold text-teal-800">{{ report.name }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ report.description }}</p>
                </Link>
            </li>
        </ul>
    </AppLayout>
</template>
