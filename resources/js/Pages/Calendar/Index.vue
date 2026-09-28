<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    view: String,
    date: String,
    range: Object,
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
const dropDate = ref('');

function calendarHref(viewKey) {
    return `/calendar?view=${viewKey}&date=${props.date}`;
}

const rangeLabel = computed(() => {
    const start = new Date(props.range.start);
    const end = new Date(props.range.end);

    return `${start.toLocaleDateString()} – ${end.toLocaleDateString()}`;
});

function onDragStart(eventId) {
    draggingId.value = eventId;
}

function reschedule() {
    if (!draggingId.value || !dropDate.value) {
        return;
    }

    const event = props.events.find((item) => item.id === draggingId.value);
    if (!event) {
        return;
    }

    const originalStart = new Date(event.starts_at);
    const originalEnd = new Date(event.ends_at);
    const durationMs = originalEnd.getTime() - originalStart.getTime();
    const nextStart = new Date(`${dropDate.value}T${originalStart.toTimeString().slice(0, 8)}`);
    const nextEnd = new Date(nextStart.getTime() + durationMs);

    router.patch(`/events/${event.id}/reschedule`, {
        starts_at: nextStart.toISOString(),
        ends_at: nextEnd.toISOString(),
    }, {
        preserveScroll: true,
        onSuccess: () => {
            draggingId.value = null;
            dropDate.value = '';
        },
    });
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
                    class="px-3 py-2 text-sm font-medium"
                    :class="view === item.key
                        ? 'bg-teal-700 text-white'
                        : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
                >
                    {{ item.label }}
                </Link>
            </div>

            <div class="flex flex-wrap gap-3">
                <Link
                    v-if="can.create_event"
                    href="/events/create"
                    class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
                >
                    New Event
                </Link>
                <Link
                    v-if="can.create_task"
                    href="/tasks/create"
                    class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    New Task
                </Link>
            </div>
        </div>

        <p class="mb-4 text-sm text-slate-600">
            Showing <span class="font-medium text-slate-900">{{ view }}</span> view for
            <span class="font-medium text-slate-900">{{ date }}</span>
            <span class="text-slate-500">({{ rangeLabel }})</span>
        </p>

        <div class="mb-6 flex flex-wrap items-end gap-3 border border-slate-200 bg-white p-4 text-sm shadow-sm">
            <p class="w-full text-slate-600">Drag an event, choose a new date, then apply to reschedule.</p>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Selected event</label>
                <p class="mt-1 font-medium text-slate-900">{{ draggingId ? `#${draggingId}` : 'None' }}</p>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500" for="drop-date">New date</label>
                <input id="drop-date" v-model="dropDate" type="date" class="mt-1 border border-slate-300 px-2 py-1.5" />
            </div>
            <button
                type="button"
                class="bg-teal-700 px-3 py-2 font-semibold text-white hover:bg-teal-800 disabled:opacity-60"
                :disabled="!draggingId || !dropDate"
                @click="reschedule"
            >
                Reschedule
            </button>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3">
                    <h2 class="text-lg font-semibold text-slate-950">Events in range</h2>
                </div>
                <ul v-if="events.length" class="divide-y divide-slate-100 text-sm">
                    <li
                        v-for="event in events"
                        :key="event.id"
                        class="cursor-grab px-4 py-3 hover:bg-slate-50 active:cursor-grabbing"
                        draggable="true"
                        @dragstart="onDragStart(event.id)"
                    >
                        <Link :href="`/events/${event.id}`" class="font-medium text-teal-800 hover:underline">
                            {{ event.subject }}
                        </Link>
                        <p class="mt-1 text-slate-600">
                            {{ event.starts_at }} – {{ event.ends_at }}
                            <span v-if="event.location"> · {{ event.location }}</span>
                        </p>
                        <p v-if="event.assignedTo" class="text-slate-500">{{ event.assignedTo.name }}</p>
                    </li>
                </ul>
                <p v-else class="px-4 py-6 text-sm text-slate-500">No events in this range.</p>
            </section>

            <section class="border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3">
                    <h2 class="text-lg font-semibold text-slate-950">Tasks due</h2>
                </div>
                <ul v-if="tasks.length" class="divide-y divide-slate-100 text-sm">
                    <li v-for="task in tasks" :key="task.id" class="px-4 py-3 hover:bg-slate-50">
                        <Link :href="`/tasks/${task.id}`" class="font-medium text-teal-800 hover:underline">
                            {{ task.subject }}
                        </Link>
                        <p class="mt-1 text-slate-600">
                            Due {{ task.due_date }} · {{ task.status }} · {{ task.priority }}
                        </p>
                    </li>
                </ul>
                <p v-else class="px-4 py-6 text-sm text-slate-500">No tasks due in this range.</p>
            </section>
        </div>
    </AppLayout>
</template>
