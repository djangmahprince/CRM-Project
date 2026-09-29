<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    report: String,
    title: String,
    columns: Array,
    rows: Array,
    recordCount: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <AppLayout :title="title">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">{{ recordCount }} record{{ recordCount === 1 ? '' : 's' }}</p>
            <div class="flex flex-wrap gap-3">
                <Link
                    :href="`/reports/${report}/export?format=csv`"
                    class="ns-btn-primary"
                >
                    Export CSV
                </Link>
                <Link
                    :href="`/reports/${report}/export?format=xlsx`"
                    class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Export Excel
                </Link>
                <Link
                    :href="`/reports/${report}/export?format=pdf`"
                    class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Export PDF
                </Link>
                <Link href="/reports" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Back to reports
                </Link>
            </div>
        </div>

        <div class="overflow-x-auto border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th v-for="column in columns" :key="column" class="px-4 py-3 font-semibold">
                            {{ column }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="(row, index) in rows" :key="index" class="hover:bg-slate-50">
                        <td v-for="(value, cellIndex) in Object.values(row)" :key="cellIndex" class="px-4 py-3 text-slate-700">
                            {{ value ?? '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <p v-if="rows.length === 0" class="px-4 py-6 text-sm text-slate-500">
                No rows match this report.
            </p>
        </div>
    </AppLayout>
</template>
