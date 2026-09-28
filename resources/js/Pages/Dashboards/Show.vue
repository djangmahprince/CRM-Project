<script setup>
import { Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    dashboard: Object,
    widgets: Array,
});

let timer;

onMounted(() => {
    if (props.dashboard.auto_refresh_minutes) {
        timer = window.setInterval(() => {
            router.reload({ only: ['widgets'] });
        }, props.dashboard.auto_refresh_minutes * 60 * 1000);
    }
});

onUnmounted(() => {
    if (timer) {
        window.clearInterval(timer);
    }
});
</script>

<template>
    <AppLayout :title="dashboard.name">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link href="/crm-dashboards" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                All dashboards
            </Link>
            <p v-if="dashboard.auto_refresh_minutes" class="self-center text-sm text-slate-500">
                Auto-refresh every {{ dashboard.auto_refresh_minutes }} minutes
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section
                v-for="widget in widgets"
                :key="widget.id"
                class="border border-slate-200 bg-white p-6 shadow-sm"
                :style="{ gridColumn: `span ${Math.min(widget.w > 6 ? 2 : 1, 2)}` }"
            >
                <h2 class="text-lg font-semibold text-slate-950">{{ widget.title }}</h2>
                <p v-if="widget.report" class="mt-1 text-xs uppercase tracking-wide text-slate-500">{{ widget.report.name }}</p>

                <ul v-if="widget.rows?.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="(row, index) in widget.rows.slice(0, 8)" :key="index" class="py-2 text-slate-700">
                        {{ Object.values(row).join(' · ') }}
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No widget data.</p>
            </section>
        </div>
    </AppLayout>
</template>
