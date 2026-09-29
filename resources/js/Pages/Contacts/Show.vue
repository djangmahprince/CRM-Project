<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import NotesAttachments from '../../Components/NotesAttachments.vue';

const props = defineProps({
    contact: Object,
    can: Object,
});

const name = [props.contact.first_name, props.contact.last_name].filter(Boolean).join(' ');

function destroyContact() {
    if (!window.confirm('Delete this contact?')) {
        return;
    }

    router.delete(`/contacts/${props.contact.id}`);
}
</script>

<template>
    <AppLayout :title="name">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.update"
                :href="`/contacts/${contact.id}/edit`"
                class="ns-btn-primary"
            >
                Edit
            </Link>
            <button
                v-if="can.delete"
                type="button"
                class="ns-btn-danger"
                @click="destroyContact"
            >
                Delete
            </button>
            <Link href="/contacts" class="ns-btn-secondary">
                Back to list
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Contact details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Account</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="contact.account"
                                :href="`/accounts/${contact.account.id}`"
                                class="ns-link"
                            >
                                {{ contact.account.name }}
                            </Link>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Title</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.title || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.email || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.phone || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Department</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.department || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Lead source</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.lead_source || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Reports to</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="contact.reportsTo"
                                :href="`/contacts/${contact.reportsTo.id}`"
                                class="ns-link"
                            >
                                {{ [contact.reportsTo.first_name, contact.reportsTo.last_name].filter(Boolean).join(' ') }}
                            </Link>
                            <span v-else>—</span>
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ contact.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="mt-6 ns-card p-6">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-950">Cases</h2>
                <Link
                    :href="`/cases/create?contact_id=${contact.id}&account_id=${contact.account_id || ''}`"
                    class="text-sm font-medium ns-link"
                >
                    New case
                </Link>
            </div>
            <ul v-if="contact.cases?.length" class="divide-y divide-slate-100 text-sm">
                <li v-for="caseItem in contact.cases" :key="caseItem.id" class="flex items-center justify-between py-2">
                    <div>
                        <Link :href="`/cases/${caseItem.id}`" class="font-medium ns-link">
                            {{ caseItem.case_number }}
                        </Link>
                        <p class="text-slate-600">{{ caseItem.subject }}</p>
                    </div>
                    <span class="text-slate-500">{{ caseItem.status }}</span>
                </li>
            </ul>
            <p v-else class="text-sm text-slate-500">No related cases.</p>
        </section>

        <NotesAttachments
            notable-type="contact"
            :notable-id="contact.id"
            :notes="contact.notes || []"
            :attachments="contact.attachments || []"
        />
    </AppLayout>
</template>
