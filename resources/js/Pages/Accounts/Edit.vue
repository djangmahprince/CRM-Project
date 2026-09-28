<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    account: Object,
    picklists: Object,
    accounts: Array,
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const form = useForm({
    name: props.account.name ?? '',
    parent_account_id: props.account.parent_account_id ?? '',
    phone: props.account.phone ?? '',
    website: props.account.website ?? '',
    type: props.account.type ?? '',
    industry: props.account.industry ?? '',
    employees: props.account.employees ?? '',
    annual_revenue: props.account.annual_revenue ?? '',
    billing_street: props.account.billing_street ?? '',
    billing_city: props.account.billing_city ?? '',
    billing_state: props.account.billing_state ?? '',
    billing_postal_code: props.account.billing_postal_code ?? '',
    billing_country: props.account.billing_country ?? '',
    shipping_street: props.account.shipping_street ?? '',
    shipping_city: props.account.shipping_city ?? '',
    shipping_state: props.account.shipping_state ?? '',
    shipping_postal_code: props.account.shipping_postal_code ?? '',
    shipping_country: props.account.shipping_country ?? '',
    description: props.account.description ?? '',
});

function submit() {
    form.put(`/accounts/${props.account.id}`);
}
</script>

<template>
    <AppLayout :title="`Edit ${account.name}`">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Account information">
                <FormField label="Account name" for-id="name" :error="form.errors.name">
                    <input id="name" v-model="form.name" type="text" required :class="inputClass" />
                </FormField>

                <FormField label="Type" for-id="type" :error="form.errors.type">
                    <select id="type" v-model="form.type" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.account_types" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Industry" for-id="industry" :error="form.errors.industry">
                    <select id="industry" v-model="form.industry" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.industries" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Parent account" for-id="parent_account_id" :error="form.errors.parent_account_id">
                    <select id="parent_account_id" v-model="form.parent_account_id" :class="inputClass">
                        <option value="">None</option>
                        <option v-for="item in accounts" :key="item.id" :value="item.id">{{ item.name }}</option>
                    </select>
                </FormField>

                <FormField label="Phone" for-id="phone" :error="form.errors.phone">
                    <input id="phone" v-model="form.phone" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Website" for-id="website" :error="form.errors.website">
                    <input id="website" v-model="form.website" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Employees" for-id="employees" :error="form.errors.employees">
                    <input id="employees" v-model="form.employees" type="number" min="0" :class="inputClass" />
                </FormField>

                <FormField label="Annual revenue" for-id="annual_revenue" :error="form.errors.annual_revenue">
                    <input id="annual_revenue" v-model="form.annual_revenue" type="number" min="0" step="0.01" :class="inputClass" />
                </FormField>

                <FormField label="Description" for-id="description" :error="form.errors.description" class="md:col-span-2">
                    <textarea id="description" v-model="form.description" rows="4" :class="inputClass" />
                </FormField>
            </FormSection>

            <FormSection title="Billing address">
                <FormField label="Street" for-id="billing_street" :error="form.errors.billing_street">
                    <input id="billing_street" v-model="form.billing_street" type="text" :class="inputClass" />
                </FormField>
                <FormField label="City" for-id="billing_city" :error="form.errors.billing_city">
                    <input id="billing_city" v-model="form.billing_city" type="text" :class="inputClass" />
                </FormField>
                <FormField label="State" for-id="billing_state" :error="form.errors.billing_state">
                    <input id="billing_state" v-model="form.billing_state" type="text" :class="inputClass" />
                </FormField>
                <FormField label="Postal code" for-id="billing_postal_code" :error="form.errors.billing_postal_code">
                    <input id="billing_postal_code" v-model="form.billing_postal_code" type="text" :class="inputClass" />
                </FormField>
                <FormField label="Country" for-id="billing_country" :error="form.errors.billing_country">
                    <input id="billing_country" v-model="form.billing_country" type="text" :class="inputClass" />
                </FormField>
            </FormSection>

            <FormSection title="Shipping address">
                <FormField label="Street" for-id="shipping_street" :error="form.errors.shipping_street">
                    <input id="shipping_street" v-model="form.shipping_street" type="text" :class="inputClass" />
                </FormField>
                <FormField label="City" for-id="shipping_city" :error="form.errors.shipping_city">
                    <input id="shipping_city" v-model="form.shipping_city" type="text" :class="inputClass" />
                </FormField>
                <FormField label="State" for-id="shipping_state" :error="form.errors.shipping_state">
                    <input id="shipping_state" v-model="form.shipping_state" type="text" :class="inputClass" />
                </FormField>
                <FormField label="Postal code" for-id="shipping_postal_code" :error="form.errors.shipping_postal_code">
                    <input id="shipping_postal_code" v-model="form.shipping_postal_code" type="text" :class="inputClass" />
                </FormField>
                <FormField label="Country" for-id="shipping_country" :error="form.errors.shipping_country">
                    <input id="shipping_country" v-model="form.shipping_country" type="text" :class="inputClass" />
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
                <Link :href="`/accounts/${account.id}`" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
