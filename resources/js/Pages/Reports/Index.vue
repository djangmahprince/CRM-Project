<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    reports: Array,
    customReports: Array,
    canBuild: Boolean,
});
</script>

<template>
    <AppLayout title="Reports">
        <div v-if="canBuild" class="mb-6">
            <Link
                href="/reports/builder"
                class="inline-flex ns-btn-primary"
            >
                Custom report builder
            </Link>
        </div>

        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Standard reports</h2>
        <ul class="mb-8 divide-y divide-slate-100 border border-slate-200 bg-white shadow-sm">
            <li v-for="report in reports" :key="report.key" class="p-6 hover:bg-slate-50">
                <Link :href="`/reports/${report.key}`" class="block">
                    <h3 class="text-lg font-semibold text-brand">{{ report.name }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ report.description }}</p>
                </Link>
            </li>
        </ul>

        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Custom reports</h2>
        <ul v-if="customReports?.length" class="divide-y divide-slate-100 border border-slate-200 bg-white shadow-sm">
            <li v-for="report in customReports" :key="report.id" class="p-6 hover:bg-slate-50">
                <Link :href="`/reports/builder/${report.id}`" class="block">
                    <h3 class="text-lg font-semibold text-brand">{{ report.name }}</h3>
                    <p v-if="report.description" class="mt-1 text-sm text-slate-600">{{ report.description }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ report.report_type }}</p>
                </Link>
            </li>
        </ul>
        <p v-else class="border border-slate-200 bg-white px-6 py-8 text-sm text-slate-500 shadow-sm">
            No custom reports yet.
            <Link v-if="canBuild" href="/reports/builder" class="font-medium ns-link">Build one</Link>.
        </p>
    </AppLayout>
</template>
