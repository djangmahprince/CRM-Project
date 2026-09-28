<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import FlashToast from '../Components/FlashToast.vue';

const page = usePage();
const search = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const nav = [
    { label: 'Home', href: '/dashboard' },
    { label: 'Leads', href: '/leads' },
    { label: 'Accounts', href: '/accounts' },
    { label: 'Contacts', href: '/contacts' },
    { label: 'Opportunities', href: '/opportunities' },
    { label: 'Cases', href: '/cases' },
    { label: 'Tasks', href: '/tasks' },
    { label: 'Calendar', href: '/calendar' },
    { label: 'Reports', href: '/reports' },
    { label: 'Dashboards', href: '/crm-dashboards' },
    { label: 'Search+', href: '/search/advanced' },
];

const currentPath = computed(() => page.url.split('?')[0]);

watch(search, async (value) => {
    if (!value || value.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    const response = await fetch(`/search/suggest?q=${encodeURIComponent(value)}`, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });
    const data = await response.json();
    suggestions.value = data.suggestions ?? [];
    showSuggestions.value = true;
});

function hideSuggestionsLater() {
    window.setTimeout(() => {
        showSuggestions.value = false;
    }, 150);
}

function goSearch() {
    if (!search.value || search.value.length < 2) {
        return;
    }
    showSuggestions.value = false;
    router.get('/search', { q: search.value });
}
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-4 lg:px-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <Link href="/dashboard" class="text-lg font-semibold tracking-tight text-slate-950">
                        Northstar CRM
                    </Link>

                    <div class="flex flex-1 items-center justify-end gap-4">
                        <div class="relative hidden w-full max-w-xs md:block">
                            <label class="sr-only" for="global-search">Search</label>
                            <form @submit.prevent="goSearch">
                                <input
                                    id="global-search"
                                    v-model="search"
                                    type="search"
                                    placeholder="Search (2+ characters)"
                                    class="w-full border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-teal-600 focus:ring-2 focus:ring-teal-100"
                                    @focus="showSuggestions = suggestions.length > 0"
                                    @blur="hideSuggestionsLater"
                                />
                            </form>
                            <div
                                v-if="showSuggestions && suggestions.length"
                                class="absolute z-20 mt-1 w-full border border-slate-200 bg-white shadow-sm"
                            >
                                <Link
                                    v-for="item in suggestions"
                                    :key="`${item.object}-${item.id}`"
                                    :href="item.url"
                                    class="block px-3 py-2 text-sm hover:bg-slate-50"
                                >
                                    <span class="font-medium text-slate-900">{{ item.label }}</span>
                                    <span class="ml-2 text-xs uppercase text-slate-400">{{ item.object }}</span>
                                    <p v-if="item.subtitle" class="text-xs text-slate-500">{{ item.subtitle }}</p>
                                </Link>
                            </div>
                        </div>
                        <button type="button" class="relative text-slate-400" aria-label="Notifications coming soon" disabled>
                            <span class="text-sm font-medium">Alerts</span>
                        </button>
                        <div class="border-l border-slate-200 pl-4 text-sm text-slate-600">
                            <p class="font-medium text-slate-900">{{ page.props.auth.user.name }}</p>
                            <p class="text-xs text-slate-500">{{ page.props.auth.roles?.[0] ?? 'User' }}</p>
                        </div>
                        <Link href="/logout" method="post" as="button" class="text-sm font-medium text-slate-600 transition hover:text-slate-950">
                            Sign out
                        </Link>
                        <Link href="/settings/mfa" class="text-sm font-medium text-slate-600 transition hover:text-slate-950">
                            MFA
                        </Link>
                    </div>
                </div>

                <nav class="flex flex-wrap gap-1 overflow-x-auto text-sm font-medium" aria-label="Primary navigation">
                    <template v-for="item in nav" :key="item.label">
                        <Link
                            v-if="item.href"
                            :href="item.href"
                            class="rounded px-3 py-2 transition"
                            :class="currentPath.startsWith(item.href) ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-950'"
                        >
                            {{ item.label }}
                        </Link>
                        <span
                            v-else
                            class="cursor-not-allowed rounded px-3 py-2 text-slate-300"
                            :aria-label="`${item.label} coming soon`"
                        >
                            {{ item.label }}
                        </span>
                    </template>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">Sales and service workspace</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ title }}</h1>
            </div>
            <slot />
        </main>

        <FlashToast />
    </div>
</template>
