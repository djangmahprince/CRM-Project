<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    leads: Object,
    filters: Object,
    can: Object,
    picklists: Object,
    owners: {
        type: Array,
        default: () => [],
    },
});

const q = ref(props.filters.q ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');
const selected = ref([]);
const bulkOwnerId = ref('');
const bulkStatus = ref('');

const columnStorageKey = 'crm.leads.columns';
const defaultColumns = { name: true, company: true, email: true, status: true, owner: true };
const visibleColumns = ref({
    ...defaultColumns,
    ...(JSON.parse(localStorage.getItem(columnStorageKey) || '{}')),
});

watch(visibleColumns, (value) => {
    localStorage.setItem(columnStorageKey, JSON.stringify(value));
}, { deep: true });

watch([sort, direction], () => {
    router.get('/leads', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
        recent: props.filters.recent || undefined,
    }, { preserveState: true, replace: true });
});

const allSelected = computed(() => props.leads.data.length > 0 && selected.value.length === props.leads.data.length);

function search() {
    router.get('/leads', {
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

function toggleAll() {
    selected.value = allSelected.value ? [] : props.leads.data.map((lead) => lead.id);
}

function runBulk(action) {
    if (!selected.value.length) {
        return;
    }

    const payload = {
        object: 'leads',
        ids: selected.value,
        action,
    };

    if (action === 'owner') {
        if (!bulkOwnerId.value) {
            return;
        }
        payload.owner_id = Number(bulkOwnerId.value);
    }

    if (action === 'status') {
        if (!bulkStatus.value) {
            return;
        }
        payload.status = bulkStatus.value;
    }

    if (action === 'delete' && !window.confirm(`Delete ${selected.value.length} leads?`)) {
        return;
    }

    router.post('/bulk-actions', payload, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = [];
        },
    });
}
</script>

<template>
    <AppLayout title="Leads">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap gap-3" @submit.prevent="search">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search name, company, email"
                    class="w-72 border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                />
                <button type="submit" class="bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Search</button>
                <Link
                    href="/leads"
                    :data="{ recent: 1 }"
                    class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Recently viewed
                </Link>
            </form>

            <Link
                v-if="can.create"
                href="/leads/create"
                class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
            >
                New Lead
            </Link>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-4 text-sm">
            <span class="font-medium text-slate-700">Columns:</span>
            <label v-for="(enabled, key) in visibleColumns" :key="key" class="flex items-center gap-2 text-slate-600">
                <input v-model="visibleColumns[key]" type="checkbox" class="accent-teal-700" />
                {{ key }}
            </label>
        </div>

        <EmptyState
            v-if="leads.data.length === 0"
            title="No leads yet"
            description="Create your first lead to start the sales pipeline."
        >
            <Link
                v-if="can.create"
                href="/leads/create"
                class="inline-flex bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
            >
                New Lead
            </Link>
        </EmptyState>

        <div v-else class="overflow-hidden border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center gap-3 border-b border-slate-200 px-4 py-3 text-sm">
                <span class="text-slate-600">{{ selected.length }} selected</span>
                <select v-model="bulkOwnerId" class="border border-slate-300 px-2 py-1">
                    <option value="">Change owner…</option>
                    <option v-for="owner in owners" :key="owner.id" :value="owner.id">{{ owner.name }}</option>
                </select>
                <button type="button" class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50" @click="runBulk('owner')">Apply owner</button>
                <select v-model="bulkStatus" class="border border-slate-300 px-2 py-1">
                    <option value="">Change status…</option>
                    <option v-for="status in (picklists?.lead_statuses || [])" :key="status" :value="status">{{ status }}</option>
                </select>
                <button type="button" class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50" @click="runBulk('status')">Apply status</button>
                <button type="button" class="border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50" @click="runBulk('delete')">Delete</button>
            </div>

            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">
                            <input type="checkbox" :checked="allSelected" class="accent-teal-700" @change="toggleAll" />
                        </th>
                        <th v-if="visibleColumns.name" class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('last_name')">Name</button>
                        </th>
                        <th v-if="visibleColumns.company" class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('company')">Company</button>
                        </th>
                        <th v-if="visibleColumns.email" class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('email')">Email</button>
                        </th>
                        <th v-if="visibleColumns.status" class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('lead_status')">Status</button>
                        </th>
                        <th v-if="visibleColumns.owner" class="px-4 py-3">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="lead in leads.data" :key="lead.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <input v-model="selected" type="checkbox" :value="lead.id" class="accent-teal-700" />
                        </td>
                        <td v-if="visibleColumns.name" class="px-4 py-3 font-medium text-slate-900">
                            <Link :href="`/leads/${lead.id}`" class="text-teal-800 hover:underline">
                                {{ [lead.first_name, lead.last_name].filter(Boolean).join(' ') }}
                            </Link>
                        </td>
                        <td v-if="visibleColumns.company" class="px-4 py-3 text-slate-700">{{ lead.company }}</td>
                        <td v-if="visibleColumns.email" class="px-4 py-3 text-slate-700">{{ lead.email || '—' }}</td>
                        <td v-if="visibleColumns.status" class="px-4 py-3 text-slate-700">{{ lead.lead_status }}</td>
                        <td v-if="visibleColumns.owner" class="px-4 py-3 text-slate-700">{{ lead.owner?.name || '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                <p>Showing {{ leads.from }}–{{ leads.to }} of {{ leads.total }}</p>
                <div class="flex gap-2">
                    <Link
                        v-if="leads.prev_page_url"
                        :href="leads.prev_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="leads.next_page_url"
                        :href="leads.next_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
