<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    lead: Object,
    can: Object,
});

const name = [props.lead.first_name, props.lead.last_name].filter(Boolean).join(' ');

function destroyLead() {
    if (!window.confirm('Delete this lead?')) {
        return;
    }

    router.delete(`/leads/${props.lead.id}`);
}
</script>

<template>
    <AppLayout :title="name">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.update"
                :href="`/leads/${lead.id}/edit`"
                class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
            >
                Edit
            </Link>
            <button
                v-if="can.delete"
                type="button"
                class="border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                @click="destroyLead"
            >
                Delete
            </button>
            <Link href="/leads" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back to list
            </Link>
        </div>

        <div v-if="lead.converted" class="mb-6 border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            This lead has been converted and is read-only.
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Lead details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Company</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.company }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.lead_status }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.email || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.phone || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Source</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.lead_source || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Rating</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.rating || '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-1 text-slate-900">{{ lead.description || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ lead.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </AppLayout>
</template>
