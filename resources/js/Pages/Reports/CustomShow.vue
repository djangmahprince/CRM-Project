<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    report: Object,
    rows: Array,
});

const columns = computed(() => {
    if (props.rows.length > 0) {
        return Object.keys(props.rows[0]);
    }

    return props.report.definition?.columns ?? [];
});

const subscriptionForm = useForm({
    frequency: 'daily',
    send_day: 1,
    send_time: '08:00',
});

function subscribe() {
    subscriptionForm.post(`/reports/${props.report.id}/subscriptions`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :title="report.name">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link href="/reports" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back to reports
            </Link>
            <Link href="/reports/builder" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                New custom report
            </Link>
        </div>

        <p v-if="report.description" class="mb-6 text-sm text-slate-600">{{ report.description }}</p>

        <section class="mb-6 border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-950">Subscribe</h2>
            <form class="mt-3 flex flex-wrap items-end gap-3 text-sm" @submit.prevent="subscribe">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-slate-500">Frequency</label>
                    <select v-model="subscriptionForm.frequency" class="mt-1 border border-slate-300 px-2 py-1.5">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-slate-500">Time</label>
                    <input v-model="subscriptionForm.send_time" type="time" class="mt-1 border border-slate-300 px-2 py-1.5" />
                </div>
                <button type="submit" class="bg-teal-700 px-3 py-2 font-semibold text-white hover:bg-teal-800">Save subscription</button>
            </form>
        </section>

        <div class="overflow-hidden border border-slate-200 bg-white shadow-sm">
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
                        <td v-for="column in columns" :key="column" class="px-4 py-3 text-slate-700">
                            {{ row[column] ?? '—' }}
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
