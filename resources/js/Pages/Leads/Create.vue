<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import LeadFormFields from '../../Components/LeadFormFields.vue';

const props = defineProps({
    picklists: Object,
});

const form = useForm({
    salutation: '',
    first_name: '',
    last_name: '',
    company: '',
    title: '',
    email: '',
    phone: '',
    mobile: '',
    lead_status: 'New',
    lead_source: '',
    rating: '',
    industry: '',
    annual_revenue: '',
    number_of_employees: '',
    website: '',
    street: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    description: '',
});

function submit() {
    form.post('/leads');
}
</script>

<template>
    <AppLayout title="New Lead">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Lead information" description="Required fields: last name and company.">
                <LeadFormFields :form="form" :picklists="picklists" :errors="form.errors" />
            </FormSection>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="ns-btn-primary disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
                <Link href="/leads" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
