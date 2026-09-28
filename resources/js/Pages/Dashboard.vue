<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

defineProps({
    title: String,
    metrics: Object,
    funnel: Array,
    revenueBySource: Array,
    todaysTasks: {
        type: Array,
        default: () => [],
    },
    todaysEvents: {
        type: Array,
        default: () => [],
    },
});

function money(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value || 0);
}
</script>

<template>
    <AppLayout :title="title || 'Good morning'">
        <div class="grid gap-5 md:grid-cols-3">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Open leads</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ metrics.open_leads }}</p>
                <Link href="/leads" class="mt-2 inline-block text-sm text-teal-700 hover:underline">View leads</Link>
            </section>
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pipeline value</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ money(metrics.pipeline_value) }}</p>
                <Link href="/opportunities" class="mt-2 inline-block text-sm text-teal-700 hover:underline">View opportunities</Link>
            </section>
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Open cases</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ metrics.open_cases }}</p>
                <Link href="/cases" class="mt-2 inline-block text-sm text-teal-700 hover:underline">View cases</Link>
            </section>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Pipeline funnel</h2>
                <ul class="mt-4 space-y-3">
                    <li v-for="row in funnel" :key="row.stage" class="flex items-center justify-between text-sm">
                        <span class="text-slate-700">{{ row.stage }}</span>
                        <span class="font-medium text-slate-900">{{ row.count }} · {{ money(row.amount) }}</span>
                    </li>
                </ul>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Expected revenue by source</h2>
                <ul v-if="revenueBySource.length" class="mt-4 space-y-3">
                    <li v-for="row in revenueBySource" :key="row.source" class="flex items-center justify-between text-sm">
                        <span class="text-slate-700">{{ row.source }}</span>
                        <span class="font-medium text-slate-900">{{ money(row.expected_revenue) }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No open opportunity amounts yet.</p>
            </section>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-950">Today's tasks</h2>
                    <Link href="/tasks" class="text-sm text-teal-700 hover:underline">All tasks</Link>
                </div>
                <ul v-if="todaysTasks.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="task in todaysTasks" :key="task.id" class="flex items-center justify-between gap-3 py-3">
                        <Link :href="`/tasks/${task.id}`" class="font-medium text-teal-800 hover:underline">{{ task.subject }}</Link>
                        <span class="text-slate-500">{{ task.status }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No tasks due today.</p>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-950">Today's events</h2>
                    <Link href="/calendar" class="text-sm text-teal-700 hover:underline">Calendar</Link>
                </div>
                <ul v-if="todaysEvents.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="event in todaysEvents" :key="event.id" class="flex items-center justify-between gap-3 py-3">
                        <Link :href="`/events/${event.id}`" class="font-medium text-teal-800 hover:underline">{{ event.subject }}</Link>
                        <span class="text-slate-500">{{ new Date(event.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No events scheduled today.</p>
            </section>
        </div>
    </AppLayout>
</template>
