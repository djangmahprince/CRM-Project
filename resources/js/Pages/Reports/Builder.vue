<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    reportTypes: Object,
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const reportTypeKeys = computed(() => Object.keys(props.reportTypes ?? {}));

const form = useForm({
    name: '',
    description: '',
    folder: 'Private Reports',
    report_type: reportTypeKeys.value[0] ?? 'leads',
    filter_field: '',
    filter_value: '',
    chart: 'none',
    columns: [],
});

const availableColumns = computed(() => props.reportTypes?.[form.report_type] ?? []);

watch(() => form.report_type, () => {
    form.columns = [];
    form.filter_field = '';
});

function toggleColumn(column) {
    if (form.columns.includes(column)) {
        form.columns = form.columns.filter((item) => item !== column);
        return;
    }

    form.columns = [...form.columns, column];
}

function submit() {
    const filters = [];
    if (form.filter_field && form.filter_value) {
        filters.push({ field: form.filter_field, value: form.filter_value });
    }

    form.transform((data) => ({
        name: data.name,
        description: data.description,
        folder: data.folder,
        report_type: data.report_type,
        definition: {
            columns: data.columns,
            filters,
            chart: data.chart,
        },
    })).post('/reports/builder');
}
</script>

<template>
    <AppLayout title="Report builder">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Report definition" description="Choose columns and optional filters for your custom report.">
                <FormField label="Name" for-id="name" :error="form.errors.name">
                    <input id="name" v-model="form.name" type="text" required :class="inputClass" />
                </FormField>

                <FormField label="Description" for-id="description" :error="form.errors.description">
                    <input id="description" v-model="form.description" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Report type" for-id="report_type" :error="form.errors.report_type">
                    <select id="report_type" v-model="form.report_type" required :class="inputClass">
                        <option v-for="type in reportTypeKeys" :key="type" :value="type">{{ type }}</option>
                    </select>
                </FormField>

                <FormField label="Chart" for-id="chart" :error="form.errors['definition.chart']">
                    <select id="chart" v-model="form.chart" :class="inputClass">
                        <option value="none">None</option>
                        <option value="bar">Bar</option>
                        <option value="pie">Pie</option>
                    </select>
                </FormField>

                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-slate-700">Columns</p>
                    <div class="mt-2 flex flex-wrap gap-3">
                        <label
                            v-for="column in availableColumns"
                            :key="column"
                            class="inline-flex items-center gap-2 rounded border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                :checked="form.columns.includes(column)"
                                class="rounded border-slate-300 text-teal-700 focus:ring-teal-600"
                                @change="toggleColumn(column)"
                            />
                            {{ column }}
                        </label>
                    </div>
                    <p v-if="form.errors['definition.columns']" class="mt-2 text-sm text-red-700">{{ form.errors['definition.columns'] }}</p>
                </div>

                <FormField label="Filter field (optional)" for-id="filter_field">
                    <select id="filter_field" v-model="form.filter_field" :class="inputClass">
                        <option value="">None</option>
                        <option v-for="column in availableColumns" :key="column" :value="column">{{ column }}</option>
                    </select>
                </FormField>

                <FormField label="Filter value" for-id="filter_value">
                    <input id="filter_value" v-model="form.filter_value" type="text" :class="inputClass" />
                </FormField>
            </FormSection>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save report' }}
                </button>
                <Link href="/reports" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
