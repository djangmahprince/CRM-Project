<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    tasks: Object,
    filters: Object,
    can: Object,
});

const q = ref(props.filters.q ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

watch([sort, direction], () => {
    router.get('/tasks', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
        recent: props.filters.recent || undefined,
    }, { preserveState: true, replace: true });
});

function search() {
    router.get('/tasks', {
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
    <AppLayout title="Tasks">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap gap-3" @submit.prevent="search">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search subject, status"
                    class="w-72 border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                />
                <button type="submit" class="bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Search</button>
                <Link
                    href="/tasks"
                    :data="{ recent: 1 }"
                    class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Recently viewed
                </Link>
            </form>

            <Link
                v-if="can.create"
                href="/tasks/create"
                class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
            >
                New Task
            </Link>
        </div>

        <EmptyState
            v-if="tasks.data.length === 0"
            title="No tasks yet"
            description="Create tasks to track follow-ups and activities."
        >
            <Link
                v-if="can.create"
                href="/tasks/create"
                class="inline-flex bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
            >
                New Task
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
                            <button type="button" class="font-semibold" @click="toggleSort('due_date')">Due date</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('status')">Status</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('priority')">Priority</button>
                        </th>
                        <th class="px-4 py-3">Assigned to</th>
                        <th class="px-4 py-3">Related</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <Link :href="`/tasks/${task.id}`" class="text-teal-800 hover:underline">
                                {{ task.subject }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ task.due_date || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ task.status }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ task.priority }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ task.assignedTo?.name || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            <span v-if="task.related_type && task.related_id">{{ task.related_type }} #{{ task.related_id }}</span>
                            <span v-else>—</span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                <p>
                    Showing {{ tasks.from }}–{{ tasks.to }} of {{ tasks.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="tasks.prev_page_url"
                        :href="tasks.prev_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="tasks.next_page_url"
                        :href="tasks.next_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
