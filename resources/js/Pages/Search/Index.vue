<script setup>
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    q: String,
    object: String,
    results: Object,
    objects: Array,
});

const query = ref(props.q ?? '');

function search() {
    router.get('/search', {
        q: query.value || undefined,
        object: props.object || undefined,
    }, { preserveState: true, replace: true });
}

function objectLabel(key) {
    return key.charAt(0).toUpperCase() + key.slice(1);
}

function filterLink(key) {
    return {
        q: props.q || undefined,
        object: key || undefined,
    };
}
</script>

<template>
    <AppLayout title="Search">
        <form class="mb-6 flex flex-wrap gap-3" @submit.prevent="search">
            <input
                v-model="query"
                type="search"
                placeholder="Search CRM records (min. 2 characters)"
                class="min-w-[18rem] flex-1 border border-slate-300 bg-white px-3 py-2 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand-light"
            />
            <button type="submit" class="ns-btn-primary">
                Search
            </button>
            <Link href="/search/advanced" class="border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Advanced search
            </Link>
        </form>

        <div class="mb-6 flex flex-wrap gap-2">
            <Link
                href="/search"
                :data="filterLink('')"
                class="border px-3 py-1.5 text-sm"
                :class="!object ? 'border-brand bg-brand-light text-brand-dark' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
            >
                All
            </Link>
            <Link
                v-for="key in objects"
                :key="key"
                href="/search"
                :data="filterLink(key)"
                class="border px-3 py-1.5 text-sm capitalize"
                :class="object === key ? 'border-brand bg-brand-light text-brand-dark' : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'"
            >
                {{ objectLabel(key) }}
            </Link>
        </div>

        <div v-if="!q || q.length < 2" class="border border-slate-200 bg-white p-6 text-sm text-slate-600 shadow-sm">
            Enter at least two characters to search.
        </div>

        <div v-else class="space-y-6">
            <template v-for="key in objects" :key="key">
                <section
                    v-if="results[key]?.length"
                    class="border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-lg font-semibold capitalize text-slate-950">{{ objectLabel(key) }}</h2>
                    <ul class="mt-4 divide-y divide-slate-100">
                        <li v-for="row in results[key]" :key="`${row.object}-${row.id}`" class="py-3">
                            <Link :href="row.url" class="block hover:bg-slate-50">
                                <span class="font-medium text-brand">{{ row.label }}</span>
                                <span v-if="row.subtitle" class="mt-0.5 block text-sm text-slate-600">{{ row.subtitle }}</span>
                            </Link>
                        </li>
                    </ul>
                </section>
            </template>

            <p
                v-if="objects.every((key) => !results[key]?.length)"
                class="border border-slate-200 bg-white p-6 text-sm text-slate-600 shadow-sm"
            >
                No results for “{{ q }}”.
            </p>
        </div>
    </AppLayout>
</template>
