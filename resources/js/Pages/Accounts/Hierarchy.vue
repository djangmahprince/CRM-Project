<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineProps({
    tree: Array,
});
</script>

<template>
    <AppLayout title="Account hierarchy">
        <div class="mb-6 flex flex-wrap gap-3">
            <Link href="/accounts" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Accounts list
            </Link>
        </div>

        <section class="border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-950">Hierarchy tree</h2>
            <ul v-if="tree.length" class="mt-4 space-y-3 text-sm">
                <li v-for="node in tree" :key="node.id">
                    <div class="flex flex-wrap items-baseline gap-2">
                        <Link :href="`/accounts/${node.id}`" class="font-medium ns-link">{{ node.name }}</Link>
                        <span class="text-slate-500">
                            rollup: {{ node.rollups.account_count }} accounts ·
                            {{ node.rollups.total_employees }} employees ·
                            {{ node.rollups.total_revenue }}
                        </span>
                    </div>
                    <ul v-if="node.children?.length" class="mt-2 space-y-2 border-l border-slate-200 pl-4">
                        <li v-for="child in node.children" :key="child.id">
                            <Link :href="`/accounts/${child.id}`" class="font-medium ns-link">{{ child.name }}</Link>
                            <span class="ml-2 text-slate-500">{{ child.rollups.account_count }} in subtree</span>
                            <ul v-if="child.children?.length" class="mt-2 space-y-2 border-l border-slate-200 pl-4">
                                <li v-for="grand in child.children" :key="grand.id">
                                    <Link :href="`/accounts/${grand.id}`" class="ns-link">{{ grand.name }}</Link>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
            <p v-else class="mt-4 text-sm text-slate-500">No root accounts yet.</p>
        </section>
    </AppLayout>
</template>
