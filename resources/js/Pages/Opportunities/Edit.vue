<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    opportunity: Object,
    picklists: Object,
    accounts: Array,
});

const stageKeys = Object.keys(props.picklists.stages);

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const form = useForm({
    name: props.opportunity.name ?? '',
    account_id: props.opportunity.account_id ?? '',
    amount: props.opportunity.amount ?? '',
    close_date: props.opportunity.close_date ?? '',
    stage: props.opportunity.stage ?? stageKeys[0],
    type: props.opportunity.type ?? '',
    lead_source: props.opportunity.lead_source ?? '',
    next_step: props.opportunity.next_step ?? '',
    description: props.opportunity.description ?? '',
});

function submit() {
    form.put(`/opportunities/${props.opportunity.id}`);
}
</script>

<template>
    <AppLayout :title="`Edit ${opportunity.name}`">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Opportunity information">
                <FormField label="Opportunity name" for-id="name" :error="form.errors.name">
                    <input id="name" v-model="form.name" type="text" required :class="inputClass" />
                </FormField>

                <FormField label="Account" for-id="account_id" :error="form.errors.account_id">
                    <select id="account_id" v-model="form.account_id" required :class="inputClass">
                        <option value="">Select account</option>
                        <option v-for="account in accounts" :key="account.id" :value="account.id">{{ account.name }}</option>
                    </select>
                </FormField>

                <FormField label="Stage" for-id="stage" :error="form.errors.stage">
                    <select id="stage" v-model="form.stage" required :class="inputClass">
                        <option v-for="stage in stageKeys" :key="stage" :value="stage">{{ stage }}</option>
                    </select>
                </FormField>

                <FormField label="Amount" for-id="amount" :error="form.errors.amount">
                    <input id="amount" v-model="form.amount" type="number" min="0" step="0.01" :class="inputClass" />
                </FormField>

                <FormField label="Close date" for-id="close_date" :error="form.errors.close_date">
                    <input id="close_date" v-model="form.close_date" type="date" required :class="inputClass" />
                </FormField>

                <FormField label="Type" for-id="type" :error="form.errors.type">
                    <select id="type" v-model="form.type" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.opportunity_types" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Lead source" for-id="lead_source" :error="form.errors.lead_source">
                    <select id="lead_source" v-model="form.lead_source" :class="inputClass">
                        <option value="">Select</option>
                        <option v-for="option in picklists.lead_sources" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Next step" for-id="next_step" :error="form.errors.next_step">
                    <input id="next_step" v-model="form.next_step" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Description" for-id="description" :error="form.errors.description" class="md:col-span-2">
                    <textarea id="description" v-model="form.description" rows="4" :class="inputClass" />
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
                <Link :href="`/opportunities/${opportunity.id}`" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
