<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import NotesAttachments from '../../Components/NotesAttachments.vue';

const props = defineProps({
    caseRecord: Object,
    can: Object,
});

function destroyCase() {
    if (!window.confirm('Delete this case?')) {
        return;
    }

    router.delete(`/cases/${props.caseRecord.id}`);
}

function closeCase() {
    router.post(`/cases/${props.caseRecord.id}/close`);
}

function reopenCase() {
    router.post(`/cases/${props.caseRecord.id}/reopen`);
}

function contactName(contact) {
    return [contact?.first_name, contact?.last_name].filter(Boolean).join(' ');
}
</script>

<template>
    <AppLayout :title="caseRecord.case_number">
        <div v-if="caseRecord.is_closed" class="mb-6 border border-slate-300 bg-slate-100 px-4 py-3 text-sm text-slate-800">
            This case is closed<span v-if="caseRecord.closed_at"> ({{ caseRecord.closed_at }})</span>.
        </div>

        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.update"
                :href="`/cases/${caseRecord.id}/edit`"
                class="ns-btn-primary"
            >
                Edit
            </Link>
            <button
                v-if="can.close"
                type="button"
                class="ns-btn-secondary"
                @click="closeCase"
            >
                Close case
            </button>
            <button
                v-if="can.reopen"
                type="button"
                class="ns-btn-secondary"
                @click="reopenCase"
            >
                Reopen case
            </button>
            <button
                v-if="can.delete"
                type="button"
                class="ns-btn-danger"
                @click="destroyCase"
            >
                Delete
            </button>
            <Link href="/cases" class="ns-btn-secondary">
                Back to list
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Case details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Subject</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.subject || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.status }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Priority</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.priority || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Origin</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.origin || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Type</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.type || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Reason</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.reason || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Account</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="caseRecord.account"
                                :href="`/accounts/${caseRecord.account.id}`"
                                class="ns-link"
                            >
                                {{ caseRecord.account.name }}
                            </Link>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Contact</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="caseRecord.contact"
                                :href="`/contacts/${caseRecord.contact.id}`"
                                class="ns-link"
                            >
                                {{ contactName(caseRecord.contact) }}
                            </Link>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-1 text-slate-900">{{ caseRecord.description || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ caseRecord.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <NotesAttachments
            notable-type="case"
            :notable-id="caseRecord.id"
            :notes="caseRecord.notes || []"
            :attachments="caseRecord.attachments || []"
        />
    </AppLayout>
</template>
