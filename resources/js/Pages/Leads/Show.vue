<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    lead: Object,
    can: Object,
});

const inputClass = 'ns-input';

const name = [props.lead.first_name, props.lead.last_name].filter(Boolean).join(' ');

const noteForm = useForm({
    notable_type: 'lead',
    notable_id: props.lead.id,
    title: '',
    body: '',
});

const attachmentForm = useForm({
    attachable_type: 'lead',
    attachable_id: props.lead.id,
    file: null,
});

function destroyLead() {
    if (!window.confirm('Delete this lead?')) {
        return;
    }

    router.delete(`/leads/${props.lead.id}`);
}

function submitNote() {
    noteForm.post('/notes', {
        preserveScroll: true,
        onSuccess: () => noteForm.reset('title', 'body'),
    });
}

function onAttachmentChange(event) {
    attachmentForm.file = event.target.files?.[0] ?? null;
}

function submitAttachment() {
    attachmentForm.post('/attachments', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            attachmentForm.reset('file');
        },
    });
}

function contactName(contact) {
    return [contact?.first_name, contact?.last_name].filter(Boolean).join(' ');
}

const logCallHref = `/tasks/create?related_type=lead&related_id=${props.lead.id}&status=Completed&subject=${encodeURIComponent('Call')}`;
</script>

<template>
    <AppLayout :title="name">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.convert"
                :href="`/leads/${lead.id}/convert`"
                class="ns-btn-primary"
            >
                Convert
            </Link>
            <Link
                v-if="can.update"
                :href="`/leads/${lead.id}/edit`"
                class="ns-btn-secondary"
            >
                Edit
            </Link>
            <Link
                :href="logCallHref"
                class="ns-btn-secondary"
            >
                Log a Call
            </Link>
            <button
                v-if="can.delete"
                type="button"
                class="ns-btn-danger"
                @click="destroyLead"
            >
                Delete
            </button>
            <Link href="/leads" class="ns-btn-secondary">
                Back to list
            </Link>
        </div>

        <div v-if="lead.converted" class="mb-6 border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            This lead has been converted and is read-only.
            <span v-if="lead.convertedAccount || lead.convertedContact || lead.convertedOpportunity" class="mt-2 block">
                <span v-if="lead.convertedAccount">
                    Account:
                    <Link :href="`/accounts/${lead.convertedAccount.id}`" class="ns-link">
                        {{ lead.convertedAccount.name }}
                    </Link>
                </span>
                <span v-if="lead.convertedContact" class="ml-0 block sm:ml-4 sm:inline">
                    Contact:
                    <Link :href="`/contacts/${lead.convertedContact.id}`" class="ns-link">
                        {{ contactName(lead.convertedContact) }}
                    </Link>
                </span>
                <span v-if="lead.convertedOpportunity" class="ml-0 block sm:ml-4 sm:inline">
                    Opportunity:
                    <Link :href="`/opportunities/${lead.convertedOpportunity.id}`" class="ns-link">
                        {{ lead.convertedOpportunity.name }}
                    </Link>
                </span>
            </span>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Lead details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Company</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.company }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.lead_status }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.email || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.phone || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Source</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.lead_source || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Rating</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.rating || '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-1 text-slate-900">{{ lead.description || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Notes</h2>

                <form class="mt-4 space-y-4 border-b border-slate-100 pb-6" @submit.prevent="submitNote">
                    <FormField label="Title" for-id="note_title" :error="noteForm.errors.title">
                        <input id="note_title" v-model="noteForm.title" type="text" :class="inputClass" />
                    </FormField>
                    <FormField label="Body" for-id="note_body" :error="noteForm.errors.body">
                        <textarea id="note_body" v-model="noteForm.body" rows="3" required :class="inputClass" />
                    </FormField>
                    <button
                        type="submit"
                        :disabled="noteForm.processing"
                        class="ns-btn-primary disabled:opacity-60"
                    >
                        {{ noteForm.processing ? 'Saving...' : 'Add note' }}
                    </button>
                </form>

                <ul v-if="lead.notes?.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="note in lead.notes" :key="note.id" class="py-3">
                        <p v-if="note.title" class="font-medium text-slate-900">{{ note.title }}</p>
                        <p class="mt-1 whitespace-pre-wrap text-slate-700">{{ note.body }}</p>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No notes yet.</p>
            </section>

            <section class="ns-card p-6">
                <h2 class="text-lg font-semibold text-slate-950">Attachments</h2>

                <form class="mt-4 space-y-4 border-b border-slate-100 pb-6" @submit.prevent="submitAttachment">
                    <FormField label="File" for-id="attachment_file" :error="attachmentForm.errors.file">
                        <input
                            id="attachment_file"
                            type="file"
                            required
                            class="block w-full text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-800 hover:file:bg-slate-200"
                            @change="onAttachmentChange"
                        />
                    </FormField>
                    <button
                        type="submit"
                        :disabled="attachmentForm.processing"
                        class="ns-btn-primary disabled:opacity-60"
                    >
                        {{ attachmentForm.processing ? 'Uploading...' : 'Upload' }}
                    </button>
                </form>

                <ul v-if="lead.attachments?.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="attachment in lead.attachments" :key="attachment.id" class="flex items-center justify-between gap-3 py-3">
                        <span class="font-medium text-slate-900">{{ attachment.original_name }}</span>
                        <Link
                            :href="`/attachments/${attachment.id}/download`"
                            class="ns-link"
                        >
                            Download
                        </Link>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No attachments yet.</p>
            </section>
        </div>
    </AppLayout>
</template>
