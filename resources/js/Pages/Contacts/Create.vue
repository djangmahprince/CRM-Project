<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    picklists: Object,
    accounts: Array,
    prefill_account_id: {
        type: Number,
        default: null,
    },
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const form = useForm({
    account_id: props.prefill_account_id ?? '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    title: '',
    department: '',
    lead_source: '',
    mailing_street: '',
    mailing_city: '',
    mailing_state: '',
    mailing_postal_code: '',
    mailing_country: '',
});

function submit() {
    form.post('/contacts');
}
</script>

<template>
    <AppLayout title="New Contact">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Contact information" description="Required fields: account and last name.">
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
                <Link href="/contacts" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
