<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    accounts: Object,
    filters: Object,
    can: Object,
});

const q = ref(props.filters.q ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

watch([sort, direction], () => {
    router.get('/accounts', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
        recent: props.filters.recent || undefined,
    }, { preserveState: true, replace: true });
});

function search() {
    router.get('/accounts', {
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
    <AppLayout title="Accounts">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap gap-3" @submit.prevent="search">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search name, phone, website"
                    class="ns-input w-72"
                />
                <button type="submit" class="ns-btn-primary">Search</button>
                <Link
                    href="/accounts"
                    :data="{ recent: 1 }"
                    class="ns-btn-secondary"
                >
                    Recently viewed
                </Link>
            </form>

            <Link
                v-if="can.create"
                href="/accounts/create"
                class="ns-btn-primary"
            >
                New Account
            </Link>
        </div>

        <EmptyState
            v-if="accounts.data.length === 0"
            title="No accounts yet"
            description="Create your first account to organize contacts and opportunities."
        >
            <Link
                v-if="can.create"
                href="/accounts/create"
                class="inline-flex ns-btn-primary"
            >
                New Account
            </Link>
        </EmptyState>

        <div v-else class="ns-card overflow-hidden">
            <table class="ns-table">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('name')">Name</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('type')">Type</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('industry')">Industry</button>
                        </th>
                        <th class="px-4 py-3">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="account in accounts.data" :key="account.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <Link :href="`/accounts/${account.id}`" class="ns-link">
                                {{ account.name }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ account.type || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ account.industry || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ account.owner?.name || '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                <p>
                    Showing {{ accounts.from }}–{{ accounts.to }} of {{ accounts.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="accounts.prev_page_url"
                        :href="accounts.prev_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="accounts.next_page_url"
                        :href="accounts.next_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
