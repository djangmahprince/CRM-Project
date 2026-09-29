<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import EmptyState from '../../Components/EmptyState.vue';

const props = defineProps({
    contacts: Object,
    filters: Object,
    can: Object,
});

const q = ref(props.filters.q ?? '');
const sort = ref(props.filters.sort ?? 'created_at');
const direction = ref(props.filters.direction ?? 'desc');

watch([sort, direction], () => {
    router.get('/contacts', {
        q: q.value || undefined,
        sort: sort.value,
        direction: direction.value,
        recent: props.filters.recent || undefined,
    }, { preserveState: true, replace: true });
});

function search() {
    router.get('/contacts', {
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

function contactName(contact) {
    return [contact.first_name, contact.last_name].filter(Boolean).join(' ');
}
</script>

<template>
    <AppLayout title="Contacts">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <form class="flex flex-wrap gap-3" @submit.prevent="search">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search name, email, phone"
                    class="ns-input w-72"
                />
                <button type="submit" class="ns-btn-primary">Search</button>
                <Link
                    href="/contacts"
                    :data="{ recent: 1 }"
                    class="ns-btn-secondary"
                >
                    Recently viewed
                </Link>
            </form>

            <Link
                v-if="can.create"
                href="/contacts/create"
                class="ns-btn-primary"
            >
                New Contact
            </Link>
        </div>

        <EmptyState
            v-if="contacts.data.length === 0"
            title="No contacts yet"
            description="Add contacts and link them to accounts."
        >
            <Link
                v-if="can.create"
                href="/contacts/create"
                class="inline-flex ns-btn-primary"
            >
                New Contact
            </Link>
        </EmptyState>

        <div v-else class="ns-card overflow-hidden">
            <table class="ns-table">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('last_name')">Name</button>
                        </th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('email')">Email</button>
                        </th>
                        <th class="px-4 py-3">
                            <button type="button" class="font-semibold" @click="toggleSort('title')">Title</button>
                        </th>
                        <th class="px-4 py-3">Owner</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">
                            <Link :href="`/contacts/${contact.id}`" class="ns-link">
                                {{ contactName(contact) }}
                            </Link>
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            <Link
                                v-if="contact.account"
                                :href="`/accounts/${contact.account.id}`"
                                class="ns-link"
                            >
                                {{ contact.account.name }}
                            </Link>
                            <span v-else>—</span>
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ contact.email || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ contact.title || '—' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ contact.owner?.name || '—' }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3 text-sm text-slate-600">
                <p>
                    Showing {{ contacts.from }}–{{ contacts.to }} of {{ contacts.total }}
                </p>
                <div class="flex gap-2">
                    <Link
                        v-if="contacts.prev_page_url"
                        :href="contacts.prev_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="contacts.next_page_url"
                        :href="contacts.next_page_url"
                        class="border border-slate-300 px-3 py-1.5 hover:bg-slate-50"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
