<script setup>
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    object: String,
    logic: String,
    conditions: Array,
    fields: Array,
    results: Array,
    objects: Array,
    operators: Array,
    savedSearches: Array,
});

const object = ref(props.object);
const logic = ref(props.logic);
const conditions = ref(props.conditions.length
    ? props.conditions.map((c) => ({ ...c }))
    : [{ field: props.fields[0], operator: 'contains', value: '' }]);

const saveForm = useForm({
    name: '',
    object_type: props.object,
    logic: props.logic,
    conditions: props.conditions,
});

function runSearch() {
    router.get('/search/advanced', {
        object: object.value,
        logic: logic.value,
        conditions: conditions.value,
    }, { preserveState: true, replace: true });
}

function addCondition() {
    conditions.value.push({ field: props.fields[0], operator: 'contains', value: '' });
}

function saveSearch() {
    saveForm.object_type = object.value;
    saveForm.logic = logic.value;
    saveForm.conditions = conditions.value;
    saveForm.post('/saved-searches', { preserveScroll: true });
}

function loadSaved(search) {
    router.get('/search/advanced', {
        object: search.object_type,
        logic: search.criteria?.logic || 'and',
        conditions: search.criteria?.conditions || [],
    });
}

function deleteSaved(id) {
    router.delete(`/saved-searches/${id}`, { preserveScroll: true });
}
</script>

<template>
    <AppLayout title="Advanced search">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link href="/search" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Simple search
            </Link>
        </div>

        <section class="border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Object</label>
                    <select v-model="object" class="mt-1 w-full border border-slate-300 px-3 py-2 text-sm">
                        <option v-for="item in objects" :key="item" :value="item">{{ item }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Logic</label>
                    <select v-model="logic" class="mt-1 w-full border border-slate-300 px-3 py-2 text-sm">
                        <option value="and">AND</option>
                        <option value="or">OR</option>
                    </select>
                </div>
            </div>

            <div class="mt-4 space-y-3">
                <div v-for="(condition, index) in conditions" :key="index" class="grid gap-3 md:grid-cols-3">
                    <select v-model="condition.field" class="border border-slate-300 px-3 py-2 text-sm">
                        <option v-for="field in fields" :key="field" :value="field">{{ field }}</option>
                    </select>
                    <select v-model="condition.operator" class="border border-slate-300 px-3 py-2 text-sm">
                        <option v-for="operator in operators" :key="operator" :value="operator">{{ operator }}</option>
                    </select>
                    <input v-model="condition.value" type="text" class="border border-slate-300 px-3 py-2 text-sm" placeholder="Value" />
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" class="border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50" @click="addCondition">Add condition</button>
                <button type="button" class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800" @click="runSearch">Run search</button>
            </div>

            <div class="mt-6 flex flex-wrap items-end gap-3 border-t border-slate-100 pt-4">
                <div>
                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Save as</label>
                    <input v-model="saveForm.name" type="text" class="mt-1 border border-slate-300 px-3 py-2 text-sm" placeholder="Search name" />
                </div>
                <button type="button" class="border border-slate-300 px-3 py-2 text-sm hover:bg-slate-50" @click="saveSearch">Save search</button>
            </div>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <section class="border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
                <h2 class="text-lg font-semibold text-slate-950">Results</h2>
                <ul v-if="results.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="row in results" :key="row.id" class="py-3">
                        <Link :href="row.url" class="font-medium text-teal-800 hover:underline">{{ row.label }}</Link>
                        <p v-if="row.subtitle" class="text-slate-500">{{ row.subtitle }}</p>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No results yet. Add conditions and run the search.</p>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Saved searches</h2>
                <ul v-if="savedSearches.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="search in savedSearches" :key="search.id" class="flex items-center justify-between gap-2 py-3">
                        <button type="button" class="text-left font-medium text-teal-800 hover:underline" @click="loadSaved(search)">
                            {{ search.name }}
                        </button>
                        <button type="button" class="text-red-600 hover:underline" @click="deleteSaved(search.id)">Delete</button>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No saved searches.</p>
            </section>
        </div>
    </AppLayout>
</template>
