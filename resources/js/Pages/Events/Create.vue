<script setup>
import { Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    picklists: Object,
    users: Array,
});

const page = usePage();
const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100';

const form = useForm({
    subject: '',
    assigned_to_id: page.props.auth.user?.id ?? '',
    related_type: '',
    related_id: '',
    starts_at: '',
    ends_at: '',
    all_day: false,
    location: '',
    show_as: props.picklists.show_as?.[0] ?? 'Busy',
    is_private: false,
    description: '',
});

function submit() {
    form.post('/events');
}
</script>

<template>
    <AppLayout title="New Event">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Event information" description="Required fields: subject, assignee, start, end, and show as.">
                <FormField label="Subject" for-id="subject" :error="form.errors.subject">
                    <input id="subject" v-model="form.subject" type="text" required :class="inputClass" />
                </FormField>

                <FormField label="Assigned to" for-id="assigned_to_id" :error="form.errors.assigned_to_id">
                    <select id="assigned_to_id" v-model="form.assigned_to_id" required :class="inputClass">
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </FormField>

                <FormField label="Starts at" for-id="starts_at" :error="form.errors.starts_at">
                    <input id="starts_at" v-model="form.starts_at" type="datetime-local" required :class="inputClass" />
                </FormField>

                <FormField label="Ends at" for-id="ends_at" :error="form.errors.ends_at">
                    <input id="ends_at" v-model="form.ends_at" type="datetime-local" required :class="inputClass" />
                </FormField>

                <div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="form.all_day" type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600" />
                        All day
                    </label>
                </div>

                <div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="form.is_private" type="checkbox" class="rounded border-slate-300 text-teal-700 focus:ring-teal-600" />
                        Private
                    </label>
                </div>

                <FormField label="Location" for-id="location" :error="form.errors.location">
                    <input id="location" v-model="form.location" type="text" :class="inputClass" />
                </FormField>

                <FormField label="Show as" for-id="show_as" :error="form.errors.show_as">
                    <select id="show_as" v-model="form.show_as" required :class="inputClass">
                        <option v-for="option in picklists.show_as" :key="option" :value="option">{{ option }}</option>
                    </select>
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
                <Link href="/events" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
