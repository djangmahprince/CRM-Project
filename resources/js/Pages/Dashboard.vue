<script setup>
import { Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import CrmChart from '../Components/CrmChart.vue';

const props = defineProps({
    title: String,
    metrics: Object,
    funnel: Array,
    revenueBySource: Array,
    todaysTasks: { type: Array, default: () => [] },
    todaysEvents: { type: Array, default: () => [] },
    keyDeals: { type: Array, default: () => [] },
    recentRecords: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    assistantInsights: { type: Array, default: () => [] },
});

const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');

const funnelLabels = computed(() => props.funnel.map((row) => row.stage));
const funnelValues = computed(() => props.funnel.map((row) => row.amount));
const sourceLabels = computed(() => props.revenueBySource.map((row) => row.source));
const sourceValues = computed(() => props.revenueBySource.map((row) => row.expected_revenue));

function money(value) {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value || 0);
}

function dismiss(key) {
    router.post('/assistant/dismissals', { key }, { preserveScroll: true });
}

function applyDates() {
    router.get('/dashboard', {
        from: from.value || undefined,
        to: to.value || undefined,
    }, { preserveState: true, replace: true });
}
</script>

<template>
    <AppLayout :title="title || 'Home'">
        <form class="mb-6 flex flex-wrap items-end gap-3 border border-slate-200 bg-white p-4 shadow-sm" @submit.prevent="applyDates">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="dash-from">Close date from</label>
                <input id="dash-from" v-model="from" type="date" class="mt-1 border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="dash-to">Close date to</label>
                <input id="dash-to" v-model="to" type="date" class="mt-1 border border-slate-300 px-3 py-2 text-sm" />
            </div>
            <button type="submit" class="bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Apply</button>
        </form>

        <section v-if="assistantInsights.length" class="mb-8 border border-teal-200 bg-teal-50/60 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Assistant</h2>
            <p class="mt-1 text-sm text-slate-600">Rule-based reminders — not AI predictions.</p>
            <ul class="mt-4 divide-y divide-teal-100 text-sm">
                <li v-for="insight in assistantInsights" :key="insight.key" class="flex flex-wrap items-start justify-between gap-3 py-3">
                    <div>
                        <Link :href="insight.url" class="font-medium text-teal-900 hover:underline">{{ insight.title }}</Link>
                        <p class="mt-1 text-slate-600">{{ insight.body }}</p>
                    </div>
                    <button type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800" @click="dismiss(insight.key)">
                        Dismiss
                    </button>
                </li>
            </ul>
        </section>

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Open leads</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ metrics.open_leads }}</p>
                <Link href="/leads" class="mt-2 inline-block text-sm text-teal-700 hover:underline">View leads</Link>
            </section>
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Pipeline value</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ money(metrics.pipeline_value) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ metrics.open_opportunities }} open · {{ money(metrics.pipeline_amount) }} amount</p>
                <Link href="/opportunities" class="mt-2 inline-block text-sm text-teal-700 hover:underline">View opportunities</Link>
            </section>
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Open cases</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ metrics.open_cases }}</p>
                <Link href="/cases" class="mt-2 inline-block text-sm text-teal-700 hover:underline">View cases</Link>
            </section>
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Today</p>
                <p class="mt-4 text-3xl font-semibold tracking-tight text-slate-950">{{ todaysTasks.length + todaysEvents.length }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ todaysTasks.length }} tasks · {{ todaysEvents.length }} events</p>
            </section>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Pipeline funnel</h2>
                <CrmChart class="mt-4" type="bar" :labels="funnelLabels" :values="funnelValues" label="Amount" />
                <ul class="mt-4 space-y-2 text-sm">
                    <li v-for="row in funnel" :key="row.stage" class="flex items-center justify-between">
                        <Link :href="`/opportunities?q=${encodeURIComponent(row.stage)}`" class="text-teal-800 hover:underline">{{ row.stage }}</Link>
                        <span class="font-medium text-slate-900">{{ row.count }} · {{ money(row.amount) }} · {{ row.percent }}%</span>
                    </li>
                </ul>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Expected revenue by source</h2>
                <CrmChart
                    v-if="revenueBySource.length"
                    class="mt-4"
                    type="doughnut"
                    :labels="sourceLabels"
                    :values="sourceValues"
                    label="Expected revenue"
                />
                <p v-else class="mt-4 text-sm text-slate-500">No open opportunity amounts yet.</p>
                <ul v-if="revenueBySource.length" class="mt-4 space-y-2 text-sm">
                    <li v-for="row in revenueBySource" :key="row.source" class="flex items-center justify-between">
                        <span class="text-slate-700">{{ row.source }}</span>
                        <span class="font-medium text-slate-900">{{ money(row.expected_revenue) }}</span>
                    </li>
                </ul>
            </section>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-950">Key deals</h2>
                    <Link href="/opportunities" class="text-sm text-teal-700 hover:underline">All opportunities</Link>
                </div>
                <ul v-if="keyDeals.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="deal in keyDeals" :key="deal.id" class="flex flex-wrap items-center justify-between gap-3 py-3">
                        <div>
                            <Link :href="`/opportunities/${deal.id}`" class="font-medium text-teal-800 hover:underline">{{ deal.name }}</Link>
                            <p class="text-slate-500">{{ deal.account || 'No account' }} · {{ deal.stage }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">{{ money(deal.amount) }}</p>
                            <p class="text-xs text-slate-500">Exp. {{ money(deal.expected_revenue) }}</p>
                        </div>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No open deals yet.</p>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-950">Recently viewed</h2>
                <ul v-if="recentRecords.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="(item, index) in recentRecords" :key="`${item.object}-${index}`" class="flex items-center justify-between gap-3 py-3">
                        <Link :href="item.url" class="font-medium text-teal-800 hover:underline">{{ item.label }}</Link>
                        <span class="text-xs uppercase tracking-wide text-slate-400">{{ item.object }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">Open a record to build your recently viewed list.</p>
            </section>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-950">Today's tasks</h2>
                    <Link href="/tasks" class="text-sm text-teal-700 hover:underline">All tasks</Link>
                </div>
                <ul v-if="todaysTasks.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="task in todaysTasks" :key="task.id" class="flex items-center justify-between gap-3 py-3">
                        <Link :href="`/tasks/${task.id}`" class="font-medium text-teal-800 hover:underline">{{ task.subject }}</Link>
                        <span class="text-slate-500">{{ task.status }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No tasks due today.</p>
            </section>

            <section class="border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-slate-950">Today's events</h2>
                    <Link href="/calendar" class="text-sm text-teal-700 hover:underline">Calendar</Link>
                </div>
                <ul v-if="todaysEvents.length" class="mt-4 divide-y divide-slate-100 text-sm">
                    <li v-for="event in todaysEvents" :key="event.id" class="flex items-center justify-between gap-3 py-3">
                        <Link :href="`/events/${event.id}`" class="font-medium text-teal-800 hover:underline">{{ event.subject }}</Link>
                        <span class="text-slate-500">{{ new Date(event.starts_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                    </li>
                </ul>
                <p v-else class="mt-4 text-sm text-slate-500">No events scheduled today.</p>
            </section>
        </div>
    </AppLayout>
</template>
