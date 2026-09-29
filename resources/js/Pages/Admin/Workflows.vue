<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    rules: Array,
    options: Object,
    can: Object,
});

const form = useForm({
    name: '',
    object_type: 'lead',
    field: 'lead_status',
    operator: 'equals',
    value: 'Qualified',
    action_type: 'create_task',
    subject_template: 'Follow up with {{name}}',
    assign_to: 'owner',
    enabled: true,
});

const fieldOptions = computed(() => {
    return form.object_type === 'opportunity'
        ? props.options.opportunity_fields
        : props.options.lead_fields;
});

const valueSuggestions = computed(() => {
    if (form.object_type === 'lead' && form.field === 'lead_status') {
        return props.options.lead_statuses;
    }
    if (form.object_type === 'lead' && form.field === 'lead_source') {
        return props.options.lead_sources;
    }
    if (form.object_type === 'lead' && form.field === 'rating') {
        return props.options.ratings;
    }
    if (form.object_type === 'opportunity' && form.field === 'stage') {
        return props.options.opportunity_stages;
    }
    if (form.object_type === 'opportunity' && form.field === 'lead_source') {
        return props.options.lead_sources;
    }
    if (form.object_type === 'opportunity' && form.field === 'type') {
        return props.options.opportunity_types;
    }
    return [];
});

function onObjectTypeChange() {
    form.field = fieldOptions.value[0] ?? '';
    form.value = valueSuggestions.value[0] ?? '';
}

function submit() {
    form.post('/admin/workflows', {
        preserveScroll: true,
        onSuccess: () => form.reset('name', 'subject_template'),
    });
}
</script>

<template>
    <AppLayout title="Workflows">
        <p class="mb-6 text-sm text-slate-600">
            Simple automation rules (Out of v1 MVP). When a matching field changes, create a task for the record owner.
            Available to System Administrator and Sales Manager.
        </p>

        <div class="mb-8 border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-slate-900">New rule</h2>
            <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submit">
                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Name</span>
                    <input v-model="form.name" type="text" required class="w-full border border-slate-300 px-3 py-2" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Object</span>
                    <select v-model="form.object_type" class="w-full border border-slate-300 px-3 py-2" @change="onObjectTypeChange">
                        <option v-for="type in options.object_types" :key="type" :value="type">{{ type }}</option>
                    </select>
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Field</span>
                    <select v-model="form.field" class="w-full border border-slate-300 px-3 py-2">
                        <option v-for="field in fieldOptions" :key="field" :value="field">{{ field }}</option>
                    </select>
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Operator</span>
                    <select v-model="form.operator" class="w-full border border-slate-300 px-3 py-2">
                        <option v-for="op in options.operators" :key="op" :value="op">{{ op }}</option>
                    </select>
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Value</span>
                    <input v-model="form.value" type="text" list="workflow-values" required class="w-full border border-slate-300 px-3 py-2" />
                    <datalist id="workflow-values">
                        <option v-for="suggestion in valueSuggestions" :key="suggestion" :value="suggestion" />
                    </datalist>
                </label>

                <label class="block text-sm">
                    <span class="mb-1 block font-medium text-slate-700">Task subject template</span>
                    <input v-model="form.subject_template" type="text" required class="w-full border border-slate-300 px-3 py-2" />
                    <p class="mt-1 text-xs text-slate-500" v-pre>Placeholders: {{name}}, {{company}}, {{lead_status}}, {{stage}}</p>
                </label>

                <label class="flex items-center gap-2 text-sm md:col-span-2">
                    <input v-model="form.enabled" type="checkbox" class="rounded border-slate-300" />
                    <span class="font-medium text-slate-700">Enabled</span>
                </label>

                <div class="md:col-span-2">
                    <button
                        type="submit"
                        class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 disabled:opacity-50"
                        :disabled="form.processing || !can.create"
                    >
                        Create rule
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">When</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="rules.length === 0">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">No workflow rules yet.</td>
                    </tr>
                    <tr v-for="rule in rules" :key="rule.id">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ rule.name }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ rule.object_type }}.{{ rule.field }} {{ rule.operator }} “{{ rule.value }}”
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            create_task → {{ rule.subject_template }}
                        </td>
                        <td class="px-4 py-3">
                            <span :class="rule.enabled ? 'text-teal-700' : 'text-slate-400'">
                                {{ rule.enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                v-if="can.manage"
                                :href="`/admin/workflows/${rule.id}`"
                                method="delete"
                                as="button"
                                class="font-medium text-red-600 hover:underline"
                            >
                                Delete
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
