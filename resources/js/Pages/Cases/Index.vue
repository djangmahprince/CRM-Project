<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    cases: Object,
    filters: Object,
    can: Object,
});

const q = ref(props.filters.q ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

watch([sort, direction], () => {
    router.get('/cases', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
        recent: props.filters.recent || undefined,
    }, { preserveState: true, replace: true });
});

function search() {
    router.get('/cases', {
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
    <AppLayout title="Cases">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap gap-3" @submit.prevent="search">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search case number, subject, status"
                    class="ns-input w-72"
                />
                <button type="submit" class="ns-btn-primary">Search</button>
                <Link
                    href="/cases"
                    :data="{ recent: 1 }"
                    class="ns-btn-secondary"
                >
                    Recently viewed
                </Link>
            </form>

            <Link
                v-if="can.create"
                href="/cases/create"
                class="ns-btn-primary"
            >
                New Case
            </Link>
        </div>

        <EmptyState
            v-if="cases.data.length === 0"
            title="No cases yet"
            description="Log customer issues and track them to resolution."
        >
            <Link
                v-if="can.create"
                href="/cases/create"
                class="inline-flex ns-btn-primary"
            >
                New Case
            </Link>
        </EmptyState>

        <div v-else class="ns-card overflow-hidden">
            <table class="ns-table">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('case_number')">Case number</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('subject')">Subject</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('status')">Status</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('priority')">Priority</button>
                        </th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="caseItem in cases.data" :key="caseItem.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <Link :href="`/cases/${caseItem.id}`" class="ns-link">
                                {{ caseItem.case_number }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ caseItem.subject || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ caseItem.status }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ caseItem.priority || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            <Link
                                v-if="caseItem.account"
                                :href="`/accounts/${caseItem.account.id}`"
                                class="ns-link"
                            >
                                {{ caseItem.account.name }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ caseItem.owner?.name || '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                <p>
                    Showing {{ cases.from }}–{{ cases.to }} of {{ cases.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="cases.prev_page_url"
                        :href="cases.prev_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="cases.next_page_url"
                        :href="cases.next_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
