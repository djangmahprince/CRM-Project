<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    task: Object,
    picklists: Object,
    users: Array,
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand-light';

function toDatetimeLocal(value) {
    if (!value) {
        return '';
    }

    return String(value).replace(' ', 'T').slice(0, 16);
}

const form = useForm({
    subject: props.task.subject ?? '',
    assigned_to_id: props.task.assigned_to_id ?? '',
    related_type: props.task.related_type ?? '',
    related_id: props.task.related_id ?? '',
    due_date: props.task.due_date ?? '',
    status: props.task.status ?? 'Not Started',
    priority: props.task.priority ?? 'Normal',
    comments: props.task.comments ?? '',
    reminder_set: props.task.reminder_set ?? false,
    reminder_at: toDatetimeLocal(props.task.reminder_at),
});

watch(() => form.reminder_set, (enabled) => {
    if (!enabled) {
        form.reminder_at = '';
    }
});

function submit() {
    form.put(`/tasks/${props.task.id}`);
}
</script>

<template>
    <AppLayout :title="`Edit ${task.subject}`">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Task information">
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
                        <input v-model="form.reminder_set" type="checkbox" class="rounded border-slate-300 text-brand focus:ring-brand" />
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
                    class="ns-btn-primary disabled:opacity-60"
                >
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
                <Link :href="`/tasks/${task.id}`" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
