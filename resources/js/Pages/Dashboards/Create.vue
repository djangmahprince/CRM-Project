<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    reports: Array,
});

const form = useForm({
    name: '',
    description: '',
    folder: 'Private Dashboards',
    auto_refresh_minutes: 5,
    widgets: [
        { title: 'Widget 1', type: 'table', report_id: props.reports[0]?.id || null },
    ],
});

function addWidget() {
    form.widgets.push({
        title: `Widget ${form.widgets.length + 1}`,
        type: 'table',
        report_id: props.reports[0]?.id || null,
    });
}

function submit() {
    form.post('/crm-dashboards');
}
</script>

<template>
    <AppLayout title="New dashboard">
        <form class="max-w-3xl space-y-4 border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Name</label>
                <input v-model="form.name" required class="mt-1 w-full border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Description</label>
                <textarea v-model="form.description" rows="2" class="mt-1 w-full border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Folder</label>
                    <input v-model="form.folder" class="mt-1 w-full border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Auto-refresh (minutes)</label>
                    <input v-model="form.auto_refresh_minutes" type="number" min="1" max="60" class="mt-1 w-full border border-slate-300 px-3 py-2 text-sm" />
                </div>
            </div>

            <div class="space-y-3 border-t border-slate-100 pt-4">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-slate-950">Widgets</h2>
                    <button type="button" class="text-sm text-teal-700 hover:underline" @click="addWidget">Add widget</button>
                </div>
                <div v-for="(widget, index) in form.widgets" :key="index" class="grid gap-3 md:grid-cols-3">
                    <input v-model="widget.title" class="border border-slate-300 px-3 py-2 text-sm" placeholder="Title" />
                    <select v-model="widget.type" class="border border-slate-300 px-3 py-2 text-sm">
                        <option value="table">Table</option>
                        <option value="metric">Metric</option>
                        <option value="chart">Chart</option>
                    </select>
                    <select v-model="widget.report_id" class="border border-slate-300 px-3 py-2 text-sm">
                        <option :value="null">No report</option>
                        <option v-for="report in reports" :key="report.id" :value="report.id">{{ report.name }}</option>
                    </select>
                </div>
            </div>

            <button type="submit" :disabled="form.processing" class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60">
                Create dashboard
            </button>
        </form>
    </AppLayout>
</template>
