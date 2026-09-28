<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    picklists: Object,
    users: Array,
    prefill: Object,
});

const page = usePage();
const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const form = useForm({
    subject: props.prefill?.subject ?? '',
    assigned_to_id: page.props.auth.user?.id ?? '',
    related_type: props.prefill?.related_type ?? '',
    related_id: props.prefill?.related_id ?? '',
    due_date: '',
    status: props.prefill?.status ?? 'Not Started',
    priority: 'Normal',
    comments: '',
    reminder_set: false,
    reminder_at: '',
});

watch(() => form.reminder_set, (enabled) => {
    if (!enabled) {
        form.reminder_at = '';
    }
});

function submit() {
    form.post('/tasks');
}
</script>

<template>
    <AppLayout title="New Task">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Task information" description="Required fields: subject, assignee, status, and priority.">
                <FormField label="Subject" for-id="subject" :error="form.errors.subject">
                    <input id="subject" v-model="form.subject" type="text" required :class="inputClass" />
                </FormField>

                <FormField label="Assigned to" for-id="assigned_to_id" :error="form.errors.assigned_to_id">
                    <select id="assigned_to_id" v-model="form.assigned_to_id" required :class="inputClass">
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </FormField>

                <FormField label="Status" for-id="status" :error="form.errors.status">
                    <select id="status" v-model="form.status" required :class="inputClass">
                        <option v-for="option in picklists.task_statuses" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Priority" for-id="priority" :error="form.errors.priority">
                    <select id="priority" v-model="form.priority" required :class="inputClass">
                        <option v-for="option in picklists.task_priorities" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Due date" for-id="due_date" :error="form.errors.due_date">
                    <input id="due_date" v-model="form.due_date" type="date" :class="inputClass" />
                </FormField>

                <FormField label="Related type" for-id="related_type" :error="form.errors.related_type">
                    <select id="related_type" v-model="form.related_type" :class="inputClass">
                        <option value="">None</option>
                        <option v-for="option in picklists.related_types" :key="option" :value="option">{{ option }}</option>
                    </select>
                </FormField>

                <FormField label="Related record ID" for-id="related_id" :error="form.errors.related_id">
                    <input id="related_id" v-model="form.related_id" type="number" min="1" :class="inputClass" />
                </FormField>

                <FormField label="Comments" for-id="comments" :error="form.errors.comments" class="md:col-span-2">
                    <textarea id="comments" v-model="form.comments" rows="4" :class="inputClass" />
                </FormField>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="form.reminder_set" type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600" />
                        Set reminder
                    </label>
                </div>

                <FormField
                    v-if="form.reminder_set"
                    label="Reminder at"
                    for-id="reminder_at"
                    :error="form.errors.reminder_at"
                >
                    <input id="reminder_at" v-model="form.reminder_at" type="datetime-local" :class="inputClass" />
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
                <Link href="/tasks" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
