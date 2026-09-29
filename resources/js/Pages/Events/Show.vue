<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    event: Object,
    can: Object,
});

function destroyEvent() {
    if (!window.confirm('Delete this event?')) {
        return;
    }

    router.delete(`/events/${props.event.id}`);
}

function relatedHref(type, id) {
    const routes = {
        lead: `/leads/${id}`,
        account: `/accounts/${id}`,
        contact: `/contacts/${id}`,
        opportunity: `/opportunities/${id}`,
        case: `/cases/${id}`,
    };

    return routes[type] ?? null;
}

function relatedLabel() {
    const related = props.event.related;
    if (!related) {
        return props.event.related_type && props.event.related_id
            ? `${props.event.related_type} #${props.event.related_id}`
            : null;
    }

    if (props.event.related_type === 'lead') {
        return [related.first_name, related.last_name].filter(Boolean).join(' ') || related.company;
    }
    if (props.event.related_type === 'contact') {
        return [related.first_name, related.last_name].filter(Boolean).join(' ');
    }
    if (props.event.related_type === 'case') {
        return related.case_number || related.subject;
    }

    return related.name || related.subject || `#${props.event.related_id}`;
}
</script>

<template>
    <AppLayout :title="event.subject">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.update"
                :href="`/events/${event.id}/edit`"
                class="ns-btn-primary"
            >
                Edit
            </Link>
            <button
                v-if="can.delete"
                type="button"
                class="border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                @click="destroyEvent"
            >
                Delete
            </button>
            <Link href="/events" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back to list
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Event details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Starts</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.starts_at }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Ends</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.ends_at }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">All day</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.all_day ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Show as</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.show_as }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Location</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.location || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Private</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.is_private ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Assigned to</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.assignedTo?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Related to</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="event.related_type && event.related_id && relatedHref(event.related_type, event.related_id)"
                                :href="relatedHref(event.related_type, event.related_id)"
                                class="ns-link"
                            >
                                {{ relatedLabel() }}
                            </Link>
                            <span v-else-if="relatedLabel()">{{ relatedLabel() }}</span>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-1 text-slate-900">{{ event.description || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ event.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </AppLayout>
</template>
