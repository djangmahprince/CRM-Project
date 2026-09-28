<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    contact: Object,
    picklists: Object,
    accounts: Array,
    contacts: Array,
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const form = useForm({
    account_id: props.contact.account_id ?? '',
    first_name: props.contact.first_name ?? '',
    last_name: props.contact.last_name ?? '',
    email: props.contact.email ?? '',
    phone: props.contact.phone ?? '',
    title: props.contact.title ?? '',
    department: props.contact.department ?? '',
    lead_source: props.contact.lead_source ?? '',
    mailing_street: props.contact.mailing_street ?? '',
    mailing_city: props.contact.mailing_city ?? '',
    mailing_state: props.contact.mailing_state ?? '',
    mailing_postal_code: props.contact.mailing_postal_code ?? '',
    mailing_country: props.contact.mailing_country ?? '',
    reports_to_id: props.contact.reports_to_id ?? '',
});

function submit() {
    form.put(`/contacts/${props.contact.id}`);
}

function contactLabel(item) {
    return [item.first_name, item.last_name].filter(Boolean).join(' ');
}
</script>

<template>
    <AppLayout :title="`Edit ${[contact.first_name, contact.last_name].filter(Boolean).join(' ')}`">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Contact information">
                <FormField label="Account" for-id="account_id" :error="form.errors.account_id">
                    <select id="account_id" v-model="form.account_id" required :class="inputClass">
                        <option value="">Select account</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                    </select>
                </FormField>

                <FormField label="First name" for-id="first_name" :error="form.errors.first_name">
                    <input id="first_name" v-model="form.first_name" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Last name" for-id="last_name" :error="form.errors.last_name">
                    <input id="last_name" v-model="form.last_name" type="text" required :class="inputClass" />
                </FormField>

                <FormField label="Email" for-id="email" :error="form.errors.email">
                    <input id="email" v-model="form.email" type="email" :class="inputClass" />
                </FormField>

                <FormField label="Phone" for-id="phone" :error="form.errors.phone">
                    <input id="phone" v-model="form.phone" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Title" for-id="title" :error="form.errors.title">
                    <input id="title" v-model="form.title" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Department" for-id="department" :error="form.errors.department">
                    <input id="department" v-model="form.department" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Lead source" for-id="lead_source" :error="form.errors.lead_source">
                    <select id="lead_source" v-model="form.lead_source" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.lead_sources" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Reports to" for-id="reports_to_id" :error="form.errors.reports_to_id">
                    <select id="reports_to_id" v-model="form.reports_to_id" :class="inputClass">
                        <option value="">None</option>
                        <option v-for="item in contacts" :key="item.id" :value="item.id">{{ contactLabel(item) }}</option>
                    </select>
                </FormField>
            </FormSection>

            <FormSection title="Mailing address">
                <FormField label="Street" for-id="mailing_street" :error="form.errors.mailing_street">
                    <input id="mailing_street" v-model="form.mailing_street" type="text" :class="inputClass" />
                </FormField>
                <FormField label="City" for-id="mailing_city" :error="form.errors.mailing_city">
                    <input id="mailing_city" v-model="form.mailing_city" type="text" :class="inputClass" />
                </FormField>
                <FormField label="State" for-id="mailing_state" :error="form.errors.mailing_state">
                    <input id="mailing_state" v-model="form.mailing_state" type="text" :class="inputClass" />
                </FormField>
                <FormField label="Postal code" for-id="mailing_postal_code" :error="form.errors.mailing_postal_code">
                    <input id="mailing_postal_code" v-model="form.mailing_postal_code" type="text" :class="inputClass" />
                </FormField>
                <FormField label="Country" for-id="mailing_country" :error="form.errors.mailing_country">
                    <input id="mailing_country" v-model="form.mailing_country" type="text" :class="inputClass" />
                </FormField>
            </FormSection>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
                <Link :href="`/contacts/${contact.id}`" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
