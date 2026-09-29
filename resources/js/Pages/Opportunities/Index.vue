<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    opportunities: Object,
    filters: Object,
    stages: Array,
    can: Object,
});

const q = ref(props.filters.q ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

watch([sort, direction], () => {
    router.get('/opportunities', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
        recent: props.filters.recent || undefined,
    }, { preserveState: true, replace: true });
});

function search() {
    router.get('/opportunities', {
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
    <AppLayout title="Opportunities">
        <div class="mb-4 flex flex-wrap gap-2 text-xs text-slate-600">
            <span class="font-semibold uppercase tracking-wide text-slate-500">Stages:</span>
            <span v-for="stage in stages" :key="stage" class="rounded border border-slate-200 bg-white px-2 py-1">{{ stage }}</span>
        </div>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap gap-3" @submit.prevent="search">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search name, stage, next step"
                    class="ns-input w-72"
                />
                <button type="submit" class="ns-btn-primary">Search</button>
                <Link
                    href="/opportunities"
                    :data="{ recent: 1 }"
                    class="ns-btn-secondary"
                >
                    Recently viewed
                </Link>
            </form>

            <Link
                v-if="can.create"
                href="/opportunities/create"
                class="ns-btn-primary"
            >
                New Opportunity
            </Link>
        </div>

        <EmptyState
            v-if="opportunities.data.length === 0"
            title="No opportunities yet"
            description="Track deals through your sales pipeline."
        >
            <Link
                v-if="can.create"
                href="/opportunities/create"
                class="inline-flex ns-btn-primary"
            >
                New Opportunity
            </Link>
        </EmptyState>

        <div v-else class="ns-card overflow-hidden">
            <table class="ns-table">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('name')">Name</button>
                        </th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('stage')">Stage</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('amount')">Amount</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('close_date')">Close date</button>
                        </th>
                        <th class="px-4 py-3">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="opportunity in opportunities.data" :key="opportunity.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <Link :href="`/opportunities/${opportunity.id}`" class="ns-link">
                                {{ opportunity.name }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            <Link
                                v-if="opportunity.account"
                                :href="`/accounts/${opportunity.account.id}`"
                                class="ns-link"
                            >
                                {{ opportunity.account.name }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ opportunity.stage }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ opportunity.amount ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ opportunity.close_date || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ opportunity.owner?.name || '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                <p>
                    Showing {{ opportunities.from }}–{{ opportunities.to }} of {{ opportunities.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="opportunities.prev_page_url"
                        :href="opportunities.prev_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="opportunities.next_page_url"
                        :href="opportunities.next_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
