<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const open = ref(false);

const unread = computed(() => page.props.notifications?.unread_count ?? 0);
const recent = computed(() => page.props.notifications?.recent ?? []);

function toggle() {
    open.value = !open.value;
}

function markAll() {
    router.post('/notifications/read-all', {}, { preserveScroll: true });
}

function openNotification(item) {
    router.post(`/notifications/${item.id}/read`, {}, { preserveScroll: true });
}
</script>

<template>
    <div class="relative">
        <button
            type="button"
            class="relative inline-flex items-center justify-center rounded-full p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-950"
            aria-haspopup="true"
            :aria-expanded="open"
            aria-label="Notifications"
            @click="toggle"
        >
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 1 1-6 0v-1m6 0H9" />
            </svg>
            <span
                v-if="unread > 0"
                class="absolute -right-0.5 -top-0.5 inline-flex min-w-5 items-center justify-center rounded-full bg-brand px-1.5 text-[10px] font-semibold text-white"
            >
                {{ unread > 99 ? '99+' : unread }}
            </span>
        </button>

        <div
            v-if="open"
            class="absolute right-0 z-30 mt-2 w-80 overflow-hidden rounded-card border border-slate-200 bg-white shadow-card"
            role="menu"
        >
            <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2">
                <p class="text-sm font-semibold text-slate-900">Notifications</p>
                <button
                    v-if="unread > 0"
                    type="button"
                    class="ns-link text-xs"
                    @click="markAll"
                >
                    Mark all read
                </button>
            </div>
            <ul v-if="recent.length" class="max-h-80 divide-y divide-slate-100 overflow-y-auto">
                <li v-for="item in recent" :key="item.id">
                    <button
                        type="button"
                        class="block w-full px-3 py-2.5 text-left hover:bg-slate-50"
                        :class="item.read_at ? 'opacity-70' : ''"
                        @click="openNotification(item)"
                    >
                        <p class="text-sm font-medium text-slate-900">{{ item.data?.subject || item.type }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ item.created_at }}</p>
                    </button>
                </li>
            </ul>
            <p v-else class="px-3 py-6 text-sm text-slate-500">No notifications yet.</p>
            <div class="border-t border-slate-100 px-3 py-2">
                <Link href="/notifications" class="ns-link text-sm" @click="open = false">
                    View all
                </Link>
            </div>
        </div>
    </div>
</template>
