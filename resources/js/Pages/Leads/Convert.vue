<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    lead: Object,
    matchedAccounts: Array,
    matchedContacts: Array,
    accounts: Array,
    contacts: Array,
    stages: Object,
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const leadName = [props.lead.first_name, props.lead.last_name].filter(Boolean).join(' ');

const stageOptions = computed(() => Object.keys(props.stages ?? {}));

const form = useForm({
    account_mode: 'create',
    account_id: '',
    account_name: props.lead.company ?? '',
    contact_mode: 'create',
    contact_id: '',
    create_opportunity: false,
    opportunity_name: `${props.lead.company ?? leadName} - Opportunity`,
    opportunity_amount: '',
    opportunity_close_date: '',
    opportunity_stage: stageOptions.value[0] ?? 'Qualification',
});

function submit() {
    form.post(`/leads/${props.lead.id}/convert`);
}

function selectMatchedAccount(account) {
    form.account_mode = 'existing';
    form.account_id = account.id;
}

function selectMatchedContact(contact) {
    form.contact_mode = 'existing';
    form.contact_id = contact.id;
}

function contactLabel(contact) {
    return [contact.first_name, contact.last_name].filter(Boolean).join(' ');
}
</script>

<template>
    <AppLayout :title="`Convert ${leadName}`">
        <p class="mb-6 text-sm text-slate-600">
            Convert <span class="font-medium text-slate-900">{{ leadName }}</span> at
            <span class="font-medium text-slate-900">{{ lead.company }}</span> into account and contact records.
        </p>

        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Account" description="Create a new account or link to an existing one.">
                <div class="md:col-span-2 flex flex-wrap gap-4 text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input v-model="form.account_mode" type="radio" value="create" class="border-slate-300 text-teal-700 focus:ring-teal-600" />
                        Create new account
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input v-model="form.account_mode" type="radio" value="existing" class="border-slate-300 text-teal-700 focus:ring-teal-600" />
                        Use existing account
                    </label>
                </div>

                <FormField
                    v-if="form.account_mode === 'create'"
                    label="Account name"
                    for-id="account_name"
                    :error="form.errors.account_name"
                >
                    <input id="account_name" v-model="form.account_name" type="text" required :class="inputClass" />
                </FormField>

                <FormField
                    v-else
                    label="Existing account"
                    for-id="account_id"
                    :error="form.errors.account_id"
                >
                    <select id="account_id" v-model="form.account_id" required :class="inputClass">
                        <option value="">Select account</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                    </select>
                </FormField>

                <div v-if="matchedAccounts.length" class="md:col-span-2">
                    <p class="text-sm font-medium text-slate-700">Suggested accounts</p>
                    <ul class="mt-2 divide-y divide-slate-100 border border-slate-200 bg-slate-50 text-sm">
                        <li
                            v-for="account in matchedAccounts"
                            :key="account.id"
                            class="flex items-center justify-between gap-3 px-3 py-2"
                        >
                            <div>
                                <p class="font-medium text-slate-900">{{ account.name }}</p>
                                <p class="text-slate-600">{{ account.phone || account.website || '—' }}</p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 border border-teal-700 px-3 py-1 text-xs font-semibold text-teal-800 hover:bg-teal-50"
                                @click="selectMatchedAccount(account)"
                            >
                                Select
                            </button>
                        </li>
                    </ul>
                </div>
            </FormSection>

            <FormSection title="Contact" description="Create a new contact or link to an existing one.">
                <div class="md:col-span-2 flex flex-wrap gap-4 text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input v-model="form.contact_mode" type="radio" value="create" class="border-slate-300 text-teal-700 focus:ring-teal-600" />
                        Create new contact
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input v-model="form.contact_mode" type="radio" value="existing" class="border-slate-300 text-teal-700 focus:ring-teal-600" />
                        Use existing contact
                    </label>
                </div>

                <FormField
                    v-if="form.contact_mode === 'existing'"
                    label="Existing contact"
                    for-id="contact_id"
                    :error="form.errors.contact_id"
                >
                    <select id="contact_id" v-model="form.contact_id" required :class="inputClass">
                        <option value="">Select contact</option>
                        <option v-for="contact in contacts" :key="contact.id" :value="contact.id">
                            {{ contactLabel(contact) }} · {{ contact.email || '—' }}
                        </option>
                    </select>
                </FormField>

                <div v-if="matchedContacts.length" class="md:col-span-2">
                    <p class="text-sm font-medium text-slate-700">Suggested contacts</p>
                    <ul class="mt-2 divide-y divide-slate-100 border border-slate-200 bg-slate-50 text-sm">
                        <li
                            v-for="contact in matchedContacts"
                            :key="contact.id"
                            class="flex items-center justify-between gap-3 px-3 py-2"
                        >
                            <div>
                                <p class="font-medium text-slate-900">{{ contactLabel(contact) }}</p>
                                <p class="text-slate-600">
                                    {{ contact.email || '—' }}
                                    <span v-if="contact.account"> · {{ contact.account.name }}</span>
                                </p>
                            </div>
                            <button
                                type="button"
                                class="shrink-0 border border-teal-700 px-3 py-1 text-xs font-semibold text-teal-800 hover:bg-teal-50"
                                @click="selectMatchedContact(contact)"
                            >
                                Select
                            </button>
                        </li>
                    </ul>
                </div>
            </FormSection>

            <FormSection title="Opportunity" description="Optionally create an opportunity from this lead.">
                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input v-model="form.create_opportunity" type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600" />
                        Create opportunity
                    </label>
                </div>

                <template v-if="form.create_opportunity">
                    <FormField label="Opportunity name" for-id="opportunity_name" :error="form.errors.opportunity_name">
                        <input id="opportunity_name" v-model="form.opportunity_name" type="text" :class="inputClass" />
                    </FormField>

                    <FormField label="Amount" for-id="opportunity_amount" :error="form.errors.opportunity_amount">
                        <input id="opportunity_amount" v-model="form.opportunity_amount" type="number" min="0" step="0.01" :class="inputClass" />
                    </FormField>

                    <FormField label="Close date" for-id="opportunity_close_date" :error="form.errors.opportunity_close_date">
                        <input id="opportunity_close_date" v-model="form.opportunity_close_date" type="date" :class="inputClass" />
                    </FormField>

                    <FormField label="Stage" for-id="opportunity_stage" :error="form.errors.opportunity_stage">
                        <select id="opportunity_stage" v-model="form.opportunity_stage" :class="inputClass">
                            <option v-for="stage in stageOptions" :key="stage" :value="stage">{{ stage }}</option>
                        </select>
                    </FormField>
                </template>
            </FormSection>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60"
                >
                    {{ form.processing ? 'Converting...' : 'Convert lead' }}
                </button>
                <Link :href="`/leads/${lead.id}`" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
