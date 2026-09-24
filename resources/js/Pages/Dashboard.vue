<script setup>
import AppLayout from '../Layouts/AppLayout.vue';

const metrics = [
    { label: 'Open leads', value: '0', detail: 'Ready for the first import' },
    { label: 'Pipeline value', value: '$0', detail: 'Opportunities will appear here' },
    { label: 'Open cases', value: '0', detail: 'Service work will appear here' },
];
</script>

<template>
    <AppLayout title="Good morning">
        <div class="grid gap-5 md:grid-cols-3">
            <section v-for="metric in metrics" :key="metric.label" class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">{{ metric.label }}</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ metric.value }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ metric.detail }}</p>
            </section>
        </div>

        <section class="mt-8 border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">Foundation slice</p>
            <h2 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Your CRM workspace is ready for Leads.</h2>
            <p class="mt-3 max-w-2xl text-slate-600">
                The application shell is now connected to Inertia. The next slice will add the Lead list, validation, ownership, and database-backed tests.
            </p>
        </section>
    </AppLayout>
</template>
