<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    users: Array,
});

function anonymize(user) {
    if (!window.confirm(`Anonymize data for ${user.email}?`)) {
        return;
    }
    router.delete(`/admin/gdpr/${user.id}`);
}
</script>

<template>
    <AppLayout title="GDPR admin">
        <p class="mb-6 text-sm text-slate-600">Export or anonymize user personal data (System Administrator only).</p>
        <div class="overflow-hidden border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="user in users" :key="user.id">
                        <td class="px-4 py-3">{{ user.name }}</td>
                        <td class="px-4 py-3">{{ user.email }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a :href="`/admin/gdpr/${user.id}/export`" class="font-medium ns-link">Export</a>
                            <button type="button" class="font-medium text-red-600 hover:underline" @click="anonymize(user)">Anonymize</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
