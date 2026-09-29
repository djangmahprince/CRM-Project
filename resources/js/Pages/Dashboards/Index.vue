<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    dashboards: Array,
});

function destroyDashboard(id) {
    if (!window.confirm('Delete this dashboard?')) {
        return;
    }
    router.delete(`/crm-dashboards/${id}`);
}
</script>

<template>
    <AppLayout title="Dashboards">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-slate-600">Custom dashboards with report widgets.</p>
            <Link href="/crm-dashboards/create" class="ns-btn-primary">
                New dashboard
            </Link>
        </div>

        <div class="overflow-hidden border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Folder</th>
                        <th class="px-4 py-3">Widgets</th>
                        <th class="px-4 py-3">Refresh</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="dashboard in dashboards" :key="dashboard.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <Link :href="`/crm-dashboards/${dashboard.id}`" class="font-medium ns-link">
                                {{ dashboard.name }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ dashboard.folder }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ dashboard.widgets_count }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ dashboard.auto_refresh_minutes ? `${dashboard.auto_refresh_minutes}m` : '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" class="text-red-600 hover:underline" @click="destroyDashboard(dashboard.id)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!dashboards.length" class="px-4 py-6 text-sm text-slate-500">No dashboards yet.</p>
        </div>
    </AppLayout>
</template>
