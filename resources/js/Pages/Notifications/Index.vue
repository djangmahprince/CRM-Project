<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
});

function markAll() {
    router.post('/notifications/read-all');
}

function openNotification(item) {
    router.post(`/notifications/${item.id}/read`);
}
</script>

<template>
    <AppLayout title="Notifications">
        <div class="mb-6 flex justify-end">
            <button
                type="button"
                class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                @click="markAll"
            >
                Mark all as read
            </button>
        </div>

        <EmptyState
            v-if="notifications.length === 0"
            title="You're all caught up"
            description="Assignment, reminder, and ownership notifications will appear here."
        />

        <ul v-else class="divide-y divide-slate-100 border border-slate-200 bg-white shadow-sm">
            <li v-for="item in notifications" :key="item.id" class="flex flex-wrap items-start justify-between gap-3 px-4 py-3">
                <div>
                    <p class="text-sm font-semibold text-slate-900">{{ item.data?.subject || item.type }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wide text-slate-400">{{ item.type }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ item.created_at }}</p>
                </div>
                <div class="flex gap-2">
                    <span
                        class="rounded px-2 py-1 text-xs font-medium"
                        :class="item.read_at ? 'bg-slate-100 text-slate-600' : 'bg-teal-50 text-teal-800'"
                    >
                        {{ item.read_at ? 'Read' : 'Unread' }}
                    </span>
                    <button
                        type="button"
                        class="text-sm font-medium text-teal-700 hover:underline"
                        @click="openNotification(item)"
                    >
                        Open
                    </button>
                </div>
            </li>
        </ul>
    </AppLayout>
</template>
