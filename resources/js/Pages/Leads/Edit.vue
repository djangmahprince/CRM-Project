<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import LeadFormFields from '../../Components/LeadFormFields.vue';

const props = defineProps({
    lead: Object,
    picklists: Object,
});

const form = useForm({
    salutation: props.lead.salutation ?? '',
    first_name: props.lead.first_name ?? '',
    last_name: props.lead.last_name ?? '',
    company: props.lead.company ?? '',
    title: props.lead.title ?? '',
    email: props.lead.email ?? '',
    phone: props.lead.phone ?? '',
    mobile: props.lead.mobile ?? '',
    lead_status: props.lead.lead_status ?? 'New',
    lead_source: props.lead.lead_source ?? '',
    rating: props.lead.rating ?? '',
    industry: props.lead.industry ?? '',
    annual_revenue: props.lead.annual_revenue ?? '',
    number_of_employees: props.lead.number_of_employees ?? '',
    website: props.lead.website ?? '',
    street: props.lead.street ?? '',
    city: props.lead.city ?? '',
    state: props.lead.state ?? '',
    postal_code: props.lead.postal_code ?? '',
    country: props.lead.country ?? '',
    description: props.lead.description ?? '',
});

function submit() {
    form.put(`/leads/${props.lead.id}`);
}
</script>

<template>
    <AppLayout :title="`Edit ${[lead.first_name, lead.last_name].filter(Boolean).join(' ')}`">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Lead information">
                <LeadFormFields :form="form" :picklists="picklists" :errors="form.errors" />
            </FormSection>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
                <Link :href="`/leads/${lead.id}`" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
