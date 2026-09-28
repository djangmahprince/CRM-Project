<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    opportunity: Object,
    stages: Object,
    can: Object,
});

const stageNames = computed(() => Object.keys(props.stages));

const currentStageIndex = computed(() => stageNames.value.indexOf(props.opportunity.stage));

function destroyOpportunity() {
    if (!window.confirm('Delete this opportunity?')) {
        return;
    }

    router.delete(`/opportunities/${props.opportunity.id}`);
}
</script>

<template>
    <AppLayout :title="opportunity.name">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link
                v-if="can.update"
                :href="`/opportunities/${opportunity.id}/edit`"
                class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800"
            >
                Edit
            </Link>
            <button
                v-if="can.delete"
                type="button"
                class="border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50"
                @click="destroyOpportunity"
            >
                Delete
            </button>
            <Link href="/opportunities" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back to list
            </Link>
        </div>

        <section class="mb-6 border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Stage path</h2>
            <ol class="mt-4 flex flex-wrap gap-2">
                <li
                    v-for="(stage, index) in stageNames"
                    :key="stage"
                    class="rounded border px-3 py-1.5 text-sm"
                    :class="stage === opportunity.stage
                        ? 'border-teal-600 bg-teal-50 font-semibold text-teal-900'
                        : index < currentStageIndex
                            ? 'border-slate-300 bg-slate-50 text-slate-700'
                            : 'border-slate-200 bg-white text-slate-500'"
                >
                    {{ stage }}
                </li>
            </ol>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Opportunity details</h2>
                <dl class="mt-4 grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="text-slate-500">Account</dt>
                        <dd class="mt-1 font-medium text-slate-900">
                            <Link
                                v-if="opportunity.account"
                                :href="`/accounts/${opportunity.account.id}`"
                                class="text-teal-800 hover:underline"
                            >
                                {{ opportunity.account.name }}
                            </Link>
                            <span v-else>—</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Stage</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.stage }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Amount</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.amount ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Expected revenue</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.expected_revenue ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Close date</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.close_date || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Probability</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.probability ?? '—' }}%</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Type</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.type || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Lead source</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.lead_source || '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Next step</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.next_step || '—' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-slate-500">Description</dt>
                        <dd class="mt-1 text-slate-900">{{ opportunity.description || '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Ownership</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.owner?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Created by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.createdBy?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Last updated by</dt>
                        <dd class="mt-1 font-medium text-slate-900">{{ opportunity.updatedBy?.name || '—' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="mt-6 border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Stage history</h2>
            <ul v-if="opportunity.stageHistory?.length" class="mt-4 divide-y divide-slate-100 text-sm">
                <li v-for="entry in opportunity.stageHistory" :key="entry.id" class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <span class="font-medium text-slate-900">
                        {{ entry.from_stage || '—' }} → {{ entry.to_stage }}
                    </span>
                    <span class="text-slate-500">
                        {{ entry.changedBy?.name || 'System' }}
                        <span v-if="entry.created_at"> · {{ entry.created_at }}</span>
                    </span>
                </li>
            </ul>
            <p v-else class="mt-4 text-sm text-slate-500">No stage changes recorded yet.</p>
        </section>
    </AppLayout>
</template>
