<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    events: Object,
    filters: Object,
    can: Object,
});

const q = ref(props.filters.q ?? '');
const sort = ref(props.filters.sort ?? 'starts_at');
const direction = ref(props.filters.direction ?? 'desc');

watch([sort, direction], () => {
    router.get('/events', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
        recent: props.filters.recent || undefined,
    }, { preserveState: true, replace: true });
});

function search() {
    router.get('/events', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
    }, { preserveState: true, replace: true });
}

function toggleSort(column) {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
        return;
    }
    sort.value = column;
    direction.value = 'asc';
}
</script>

<template>
    <AppLayout title="Events">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap gap-3" @submit.prevent="search">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search subject, location"
                    class="w-72 border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand-light"
                />
                <button type="submit" class="bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Search</button>
                <Link
                    href="/events"
                    :data="{ recent: 1 }"
                    class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Recently viewed
                </Link>
            </form>

            <div class="flex flex-wrap gap-3">
                <Link
                    href="/calendar"
                    class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Calendar
                </Link>
                <Link
                    v-if="can.create"
                    href="/events/create"
                    class="ns-btn-primary"
                >
                    New Event
                </Link>
            </div>
        </div>

        <EmptyState
            v-if="events.data.length === 0"
            title="No events yet"
            description="Schedule meetings and activities on the calendar."
        >
            <Link
                v-if="can.create"
                href="/events/create"
                class="inline-flex ns-btn-primary"
            >
                New Event
            </Link>
        </EmptyState>

        <div v-else class="overflow-hidden border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('subject')">Subject</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('starts_at')">Starts</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('ends_at')">Ends</button>
                        </th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Assigned to</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="event in events.data" :key="event.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <Link :href="`/events/${event.id}`" class="ns-link">
                                {{ event.subject }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ event.starts_at }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ event.ends_at }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ event.location || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ event.assignedTo?.name || '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                <p>
                    Showing {{ events.from }}–{{ events.to }} of {{ events.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="events.prev_page_url"
                        :href="events.prev_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="events.next_page_url"
                        :href="events.next_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
