<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import FormSection from '../../Components/FormSection.vue';
import FormField from '../../Components/FormField.vue';

const props = defineProps({
    object: String,
    fields: Array,
});

const inputClass = 'block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-brand focus:ring-2 focus:ring-brand-light';

const fileChosen = ref(false);
const columnIndexes = [0, 1, 2, 3, 4, 5];

const form = useForm({
    object: props.object,
    file: null,
    mapping: {
        0: '',
        1: '',
        2: '',
        3: '',
        4: '',
        5: '',
    },
});

function onObjectChange(event) {
    router.get('/import', { object: event.target.value }, { preserveState: false });
}

function onFileChange(event) {
    const file = event.target.files?.[0] ?? null;
    form.file = file;
    fileChosen.value = Boolean(file);
}

function submit() {
    form.post('/import', {
        forceFormData: true,
    });
}
</script>

<template>
    <AppLayout title="Import CSV">
        <form class="space-y-6" @submit.prevent="submit">
            <FormSection title="Import settings" description="Upload a CSV file and map the first six columns to CRM fields.">
                <FormField label="Object" for-id="object" :error="form.errors.object">
                    <select id="object" :value="object" :class="inputClass" @change="onObjectChange">
                        <option value="leads">Leads</option>
                        <option value="accounts">Accounts</option>
                        <option value="contacts">Contacts</option>
                    </select>
                </FormField>

                <FormField label="CSV file" for-id="file" :error="form.errors.file">
                    <input
                        id="file"
                        type="file"
                        accept=".csv,.txt"
                        required
                        class="block w-full text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-slate-800 hover:file:bg-slate-200"
                        @change="onFileChange"
                    />
                </FormField>

                <div v-if="fileChosen" class="md:col-span-2 space-y-4">
                    <p class="text-sm font-medium text-slate-700">Column mapping</p>
                    <div
                        v-for="index in columnIndexes"
                        :key="index"
                        class="grid gap-3 sm:grid-cols-2 sm:items-center"
                    >
                        <label :for="`mapping-${index}`" class="text-sm text-slate-600">CSV column {{ index }}</label>
                        <select :id="`mapping-${index}`" v-model="form.mapping[index]" :class="inputClass">
                            <option value="">Skip</option>
                            <option v-for="field in fields" :key="field" :value="field">{{ field }}</option>
                        </select>
                    </div>
                    <p v-if="form.errors.mapping" class="text-sm text-red-700">{{ form.errors.mapping }}</p>
                </div>
            </FormSection>

            <div class="flex gap-3">
                <button
                    type="submit"
                    :disabled="form.processing || !fileChosen"
                    class="ns-btn-primary disabled:opacity-60"
                >
                    {{ form.processing ? 'Importing...' : 'Import' }}
                </button>
                <Link href="/dashboard" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </Link>
            </div>
        </form>
    </AppLayout>
</template>
