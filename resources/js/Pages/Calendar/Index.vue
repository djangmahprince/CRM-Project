<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    view: String,
    date: String,
    range: Object,
    navigation: Object,
    days: { type: Array, default: () => [] },
    events: Array,
    tasks: Array,
    can: Object,
});

const views = [
    { key: 'day', label: 'Day' },
    { key: 'week', label: 'Week' },
    { key: 'month', label: 'Month' },
    { key: 'table', label: 'Table' },
];

const draggingId = ref(null);
const detailEvent = ref(null);

function calendarHref(viewKey, date = props.date) {
    return `/calendar?view=${viewKey}&date=${date}`;
}

const rangeLabel = computed(() => {
    const start = new Date(props.range.start);
    const end = new Date(props.range.end);
    return `${start.toLocaleDateString()} – ${end.toLocaleDateString()}`;
});

const weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function onDragStart(eventId, e) {
    draggingId.value = eventId;
    e.dataTransfer?.setData('text/plain', String(eventId));
}

function onDrop(dayDate) {
    if (!draggingId.value) {
        return;
    }

    const event = props.events.find((item) => item.id === draggingId.value);
    if (!event) {
        return;
    }

    const originalStart = new Date(event.starts_at);
    const originalEnd = new Date(event.ends_at);
    const durationMs = originalEnd.getTime() - originalStart.getTime();
    const nextStart = new Date(`${dayDate}T${originalStart.toTimeString().slice(0, 8)}`);
    const nextEnd = new Date(nextStart.getTime() + durationMs);

    router.patch(`/events/${event.id}/reschedule`, {
        starts_at: nextStart.toISOString(),
        ends_at: nextEnd.toISOString(),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            draggingId.value = null;
        },
    });
}

function showEvent(event) {
    detailEvent.value = event;
}
</script>

<template>
    <AppLayout title="Calendar">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="item in views"
                    :key="item.key"
                    :href="calendarHref(item.key)"
                    class="rounded px-3 py-2 text-sm font-medium"
                    :class="view === item.key
                        ? 'bg-brand text-white'
                        : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
                >
                    {{ item.label }}
                </Link>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Link :href="calendarHref(view, navigation.prev)" class="border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Prev</Link>
                <Link :href="calendarHref(view, navigation.today)" class="border border-slate-300 bg-white px-3 py-2 text-sm font-medium hover:bg-slate-50">Today</Link>
                <Link :href="calendarHref(view, navigation.next)" class="border border-slate-300 bg-white px-3 py-2 text-sm hover:bg-slate-50">Next</Link>
                <Link
                    v-if="can.create_event"
                    href="/events/create"
                    class="ns-btn-primary"
                >
                    New Event
                </Link>
                <Link
                    v-if="can.create_task"
                    href="/tasks/create"
                    class="ns-btn-secondary"
                >
                    New Task
                </Link>
            </div>
        </div>

        <p class="mb-4 text-sm text-slate-600">
            Showing <span class="font-medium text-slate-900">{{ view }}</span> ·
            <span class="font-medium text-slate-900">{{ date }}</span>
            <span class="text-slate-500">({{ rangeLabel }})</span>
            <span class="ml-2 text-slate-500">Drag events onto another day to reschedule.</span>
        </p>

        <div v-if="view === 'table'" class="ns-card overflow-hidden">
            <table class="ns-table">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Starts</th>
                        <th class="px-4 py-3">Ends</th>
                        <th class="px-4 py-3">Location</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="event in events" :key="event.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <Link :href="`/events/${event.id}`" class="font-medium ns-link">{{ event.subject }}</Link>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ event.starts_at }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ event.ends_at }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ event.location || '—' }}</td>
                    </tr>
                </tbody>
            </table>
            <p v-if="!events.length" class="px-4 py-6 text-sm text-slate-500">No events in this range.</p>
        </div>

        <div v-else class="ns-card overflow-hidden">
            <div
                class="grid gap-px bg-slate-200"
                :class="view === 'day' ? 'grid-cols-1' : 'grid-cols-2 sm:grid-cols-4 lg:grid-cols-7'"
            >
                <div
                    v-for="label in (view === 'day' ? [] : weekdayLabels)"
                    :key="label"
                    class="hidden bg-slate-50 px-2 py-2 text-center text-xs font-semibold uppercase tracking-wide text-slate-500 lg:block"
                >
                    {{ label }}
                </div>
                <div
                    v-for="day in days"
                    :key="day.date"
                    class="min-h-28 bg-white p-2"
                    :class="[
                        day.is_today ? 'ring-2 ring-inset ring-brand' : '',
                        !day.is_current_month && view === 'month' ? 'bg-slate-50/80' : '',
                    ]"
                    @dragover.prevent
                    @drop.prevent="onDrop(day.date)"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <Link :href="calendarHref('day', day.date)" class="text-sm font-semibold text-slate-800 hover:text-brand">
                            {{ day.label }}
                        </Link>
                        <span v-if="day.is_today" class="text-[10px] font-semibold uppercase text-brand">Today</span>
                    </div>
                    <ul class="space-y-1">
                        <li
                            v-for="event in day.events"
                            :key="event.id"
                            class="cursor-grab rounded px-1.5 py-1 text-xs text-white active:cursor-grabbing"
                            :style="{ backgroundColor: event.color }"
                            draggable="true"
                            @dragstart="onDragStart(event.id, $event)"
                            @click="showEvent(event)"
                        >
                            {{ event.subject }}
                        </li>
                        <li
                            v-for="task in day.tasks"
                            :key="`task-${task.id}`"
                            class="rounded border border-amber-200 bg-amber-50 px-1.5 py-1 text-xs text-amber-900"
                        >
                            <Link :href="`/tasks/${task.id}`">Task: {{ task.subject }}</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div
            v-if="detailEvent"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4"
            role="dialog"
            aria-modal="true"
            @click.self="detailEvent = null"
        >
            <div class="w-full max-w-md border border-slate-200 bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-950">{{ detailEvent.subject }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ detailEvent.starts_at }} – {{ detailEvent.ends_at }}</p>
                <p v-if="detailEvent.location" class="mt-1 text-sm text-slate-600">{{ detailEvent.location }}</p>
                <div class="mt-6 flex gap-3">
                    <Link :href="`/events/${detailEvent.id}`" class="ns-btn-primary">
                        Open
                    </Link>
                    <button type="button" class="border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50" @click="detailEvent = null">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
