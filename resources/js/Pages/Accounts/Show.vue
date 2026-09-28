<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    account: Object,
    can: Object,
});

function contactName(contact) {
    return [contact.first_name, contact.last_name].filter(Boolean).join(' ');
}

function destroyAccount() {
    if (!window.confirm('Delete this account?')) {
        return;
    }

    router.delete(`/accounts/${props.account.id}`);
}
</script>

<template>
    <AppLayout :title="account.name">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.update"
                :href="`/accounts/${account.id}/edit`"
                class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
            >
                Edit
            </Link>
            <button
                v-if="can.delete"
                type="button"
                class="border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                @click="destroyAccount"
            >
                Delete
            </button>
            <Link href="/accounts" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back to list
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Account details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Type</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.type || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Industry</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.industry || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.phone || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Website</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.website || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Employees</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.employees ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Annual revenue</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.annual_revenue ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Parent account</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="account.parentAccount"
                                :href="`/accounts/${account.parentAccount.id}`"
                                class="text-teal-800 hover:underline"
                            >
                                {{ account.parentAccount.name }}
                            </Link>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-1 text-slate-900">{{ account.description || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ account.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-950">Contacts</h2>
                    <Link
                        :href="`/contacts/create?account_id=${account.id}`"
                        class="text-sm font-medium text-teal-800 hover:underline"
                    >
                        New
                    </Link>
                </div>
                <ul v-if="account.contacts?.length" class="divide-y divide-slate-100 text-sm">
                    <li v-for="contact in account.contacts" :key="contact.id" class="py-2">
                        <Link :href="`/contacts/${contact.id}`" class="font-medium text-teal-800 hover:underline">
                            {{ contactName(contact) }}
                        </Link>
                        <p class="text-slate-600">{{ contact.title || contact.email || '—' }}</p>
                    </li>
                </ul>
                <p v-else class="text-sm text-slate-500">No related contacts.</p>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-950">Opportunities</h2>
                    <Link
                        :href="`/opportunities/create?account_id=${account.id}`"
                        class="text-sm font-medium text-teal-800 hover:underline"
                    >
                        New
                    </Link>
                </div>
                <ul v-if="account.opportunities?.length" class="divide-y divide-slate-100 text-sm">
                    <li v-for="opportunity in account.opportunities" :key="opportunity.id" class="py-2">
                        <Link :href="`/opportunities/${opportunity.id}`" class="font-medium text-teal-800 hover:underline">
                            {{ opportunity.name }}
                        </Link>
                        <p class="text-slate-600">{{ opportunity.stage }} · {{ opportunity.amount ?? '—' }}</p>
                    </li>
                </ul>
                <p v-else class="text-sm text-slate-500">No related opportunities.</p>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-950">Cases</h2>
                    <Link
                        :href="`/cases/create?account_id=${account.id}`"
                        class="text-sm font-medium text-teal-800 hover:underline"
                    >
                        New
                    </Link>
                </div>
                <ul v-if="account.cases?.length" class="divide-y divide-slate-100 text-sm">
                    <li v-for="caseItem in account.cases" :key="caseItem.id" class="py-2">
                        <Link :href="`/cases/${caseItem.id}`" class="font-medium text-teal-800 hover:underline">
                            {{ caseItem.case_number }}
                        </Link>
                        <p class="text-slate-600">{{ caseItem.subject }} · {{ caseItem.status }}</p>
                    </li>
                </ul>
                <p v-else class="text-sm text-slate-500">No related cases.</p>
            </section>
        </div>
    </AppLayout>
</template>
