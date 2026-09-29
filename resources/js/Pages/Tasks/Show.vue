<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    task: Object,
    can: Object,
});

function destroyTask() {
    if (!window.confirm('Delete this task?')) {
        return;
    }

    router.delete(`/tasks/${props.task.id}`);
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
    const related = props.task.related;
    if (!related) {
        return props.task.related_type && props.task.related_id
            ? `${props.task.related_type} #${props.task.related_id}`
            : null;
    }

    if (props.task.related_type === 'lead') {
        return [related.first_name, related.last_name].filter(Boolean).join(' ') || related.company;
    }
    if (props.task.related_type === 'contact') {
        return [related.first_name, related.last_name].filter(Boolean).join(' ');
    }
    if (props.task.related_type === 'case') {
        return related.case_number || related.subject;
    }

    return related.name || related.subject || `#${props.task.related_id}`;
}
</script>

<template>
    <AppLayout :title="task.subject">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.update"
                :href="`/tasks/${task.id}/edit`"
                class="ns-btn-primary"
            >
                Edit
            </Link>
            <button
                v-if="can.delete"
                type="button"
                class="border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                @click="destroyTask"
            >
                Delete
            </button>
            <Link href="/tasks" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back to list
            </Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Task details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Status</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ task.status }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Priority</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ task.priority }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Due date</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ task.due_date || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Assigned to</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ task.assignedTo?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Related to</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="task.related_type && task.related_id && relatedHref(task.related_type, task.related_id)"
                                :href="relatedHref(task.related_type, task.related_id)"
                                class="ns-link"
                            >
                                {{ relatedLabel() }}
                            </Link>
                            <span v-else-if="relatedLabel()">{{ relatedLabel() }}</span>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Reminder</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <span v-if="task.reminder_set">{{ task.reminder_at || 'Set' }}</span>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Comments</dt>
                        <dd class="mt-1 text-slate-900">{{ task.comments || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ task.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ task.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ task.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </AppLayout>
</template>
