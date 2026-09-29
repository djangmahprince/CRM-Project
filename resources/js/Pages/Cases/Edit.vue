<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    caseRecord: Object,
    picklists: Object,
    accounts: Array,
    contacts: Array,
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand-light';

const form = useForm({
    subject: props.caseRecord.subject ?? '',
    origin: props.caseRecord.origin ?? '',
    status: props.caseRecord.status ?? 'New',
    priority: props.caseRecord.priority ?? '',
    type: props.caseRecord.type ?? '',
    reason: props.caseRecord.reason ?? '',
    account_id: props.caseRecord.account_id ?? '',
    contact_id: props.caseRecord.contact_id ?? '',
    description: props.caseRecord.description ?? '',
});

const filteredContacts = computed(() => {
    if (!form.account_id) {
        return props.contacts;
    }

    return props.contacts.filter((contact) => contact.account_id == form.account_id);
});

function submit() {
    form.put(`/cases/${props.caseRecord.id}`);
}

function contactLabel(contact) {
    return [contact.first_name, contact.last_name].filter(Boolean).join(' ');
}
</script>

<template>
    <AppLayout :title="`Edit ${caseRecord.case_number}`">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Case information">
                <FormField label="Subject" for-id="subject" :error="form.errors.subject">
                    <input id="subject" v-model="form.subject" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Origin" for-id="origin" :error="form.errors.origin">
                    <select id="origin" v-model="form.origin" required :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.case_origins" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Status" for-id="status" :error="form.errors.status">
                    <select id="status" v-model="form.status" required :class="inputClass">
                        <option v-for="option in picklists.case_statuses" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Priority" for-id="priority" :error="form.errors.priority">
                    <select id="priority" v-model="form.priority" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.priorities" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Type" for-id="type" :error="form.errors.type">
                    <select id="type" v-model="form.type" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.case_types" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Reason" for-id="reason" :error="form.errors.reason">
                    <select id="reason" v-model="form.reason" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.case_reasons" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Account" for-id="account_id" :error="form.errors.account_id">
                    <select id="account_id" v-model="form.account_id" :class="inputClass">
                        <option value="">None</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                    </select>
                </FormField>

                <FormField label="Contact" for-id="contact_id" :error="form.errors.contact_id">
                    <select id="contact_id" v-model="form.contact_id" :class="inputClass">
                        <option value="">None</option>
                        <option v-for="contact in filteredContacts" :key="contact.id" :value="contact.id">{{ contactLabel(contact) }}</option>
                    </select>
                </FormField>

                <FormField label="Description" for-id="description" :error="form.errors.description" class="md:col-span-2">
                    <textarea id="description" v-model="form.description" rows="4" :class="inputClass" />
                </FormField>
            </FormSection>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="ns-btn-primary disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
                <Link :href="`/cases/${caseRecord.id}`" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
