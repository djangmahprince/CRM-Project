<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import FlashToast from '../Components/FlashToast.vue';
import NotificationBell from '../Components/NotificationBell.vue';

const page = usePage();
const search = ref('');
const suggestions = ref([]);
const showSuggestions = ref(false);
const mobileNavOpen = ref(false);
const userMenuOpen = ref(false);

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});

const navGroups = [
    {
        label: 'Main',
        items: [{ label: 'Home', href: '/dashboard', icon: 'home' }],
    },
    {
        label: 'Sales',
        items: [
            { label: 'Leads', href: '/leads', icon: 'leads' },
            { label: 'Accounts', href: '/accounts', icon: 'accounts' },
            { label: 'Contacts', href: '/contacts', icon: 'contacts' },
            { label: 'Opportunities', href: '/opportunities', icon: 'opportunities' },
        ],
    },
    {
        label: 'Service',
        items: [
            { label: 'Cases', href: '/cases', icon: 'cases' },
            { label: 'Tasks', href: '/tasks', icon: 'tasks' },
            { label: 'Calendar', href: '/calendar', icon: 'calendar' },
        ],
    },
    {
        label: 'Insights',
        items: [
            { label: 'Reports', href: '/reports', icon: 'reports' },
            { label: 'Dashboards', href: '/crm-dashboards', icon: 'dashboards' },
        ],
    },
    {
        label: 'Tools',
        items: [
            { label: 'Search+', href: '/search/advanced', icon: 'search' },
            { label: 'Import', href: '/import', icon: 'import' },
        ],
    },
];

const currentPath = computed(() => page.url.split('?')[0]);
const appName = computed(() => page.props.appName || 'NorthStar');
const userName = computed(() => page.props.auth?.user?.name ?? 'User');
const userInitials = computed(() => {
    const parts = String(userName.value).trim().split(/\s+/).filter(Boolean);
    if (!parts.length) {
        return 'U';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }
    return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
});
const isAdmin = computed(() => page.props.auth?.roles?.includes('System Administrator'));
const canManageWorkflows = computed(() =>
    isAdmin.value || page.props.auth?.roles?.includes('Sales Manager'),
);

function isActive(href) {
    if (href === '/dashboard') {
        return currentPath.value === '/dashboard';
    }
    return currentPath.value === href || currentPath.value.startsWith(`${href}/`);
}

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

function closeMobileNav() {
    mobileNavOpen.value = false;
}

function onDocumentClick(event) {
    const target = event.target;
    if (!(target instanceof Element)) {
        return;
    }
    if (!target.closest('[data-user-menu]')) {
        userMenuOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});
</script>

<template>
    <div class="min-h-screen bg-surface text-slate-900">
        <!-- Mobile overlay -->
        <div
            v-if="mobileNavOpen"
            class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
            aria-hidden="true"
            @click="closeMobileNav"
        />

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-[260px] flex-col bg-sidebar text-white transition-transform duration-200 lg:translate-x-0"
            :class="mobileNavOpen ? 'translate-x-0' : '-translate-x-full'"
            aria-label="Primary navigation"
        >
            <div class="flex items-center gap-3 px-5 py-5">
                <svg class="size-8 shrink-0" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                    <path
                        d="M16 2.5 19.2 12.8 29.5 16 19.2 19.2 16 29.5 12.8 19.2 2.5 16 12.8 12.8 16 2.5Z"
                        fill="white"
                    />
                    <circle cx="16" cy="16" r="3.2" fill="#2447b9" />
                </svg>
                <Link href="/dashboard" class="text-lg font-semibold tracking-tight text-white" @click="closeMobileNav">
                    {{ appName }}
                </Link>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-6">
                <div v-for="group in navGroups" :key="group.label">
                    <p class="mb-1.5 px-3 text-[11px] font-semibold uppercase tracking-[0.14em] text-white/50">
                        {{ group.label }}
                    </p>
                    <ul class="space-y-0.5">
                        <li v-for="item in group.items" :key="item.href">
                            <Link
                                :href="item.href"
                                class="flex items-center gap-3 rounded-pill px-3 py-2.5 text-sm font-medium transition"
                                :class="isActive(item.href)
                                    ? 'bg-white text-brand shadow-sm'
                                    : 'text-white/90 hover:bg-white/10'"
                                @click="closeMobileNav"
                            >
                                <span class="inline-flex size-5 items-center justify-center opacity-90" aria-hidden="true">
                                    <svg v-if="item.icon === 'home'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9.5Z" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'leads'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'accounts'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'contacts'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'opportunities'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M8 8h7a3 3 0 0 1 0 6H9a3 3 0 0 0 0 6h7" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'cases'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M4 7h16v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7Z" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'tasks'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'calendar'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'reports'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5M10 19V9M16 19v-6M22 19H2" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'dashboards'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h7v7H4V4Zm9 0h7v5h-7V4ZM4 13h7v7H4v-7Zm9 3h7v4h-7v-4Z" />
                                    </svg>
                                    <svg v-else-if="item.icon === 'search'" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z" />
                                    </svg>
                                    <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12M8 11l4 4 4-4M4 19h16" />
                                    </svg>
                                </span>
                                {{ item.label }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main column -->
        <div class="lg:pl-[260px]">
            <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/90 backdrop-blur">
                <div class="flex items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-2.5 py-2 text-sm text-slate-700 lg:hidden"
                        aria-label="Toggle navigation"
                        :aria-expanded="mobileNavOpen"
                        @click="mobileNavOpen = !mobileNavOpen"
                    >
                        Menu
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs text-slate-500">
                            Application
                            <span class="mx-1 text-slate-300">/</span>
                            <span class="text-slate-700">{{ title }}</span>
                        </p>
                        <h1 class="truncate text-lg font-semibold tracking-tight text-slate-950 sm:text-xl">{{ title }}</h1>
                    </div>

                    <div class="relative hidden w-full max-w-xs md:block lg:max-w-sm">
                        <label class="sr-only" for="global-search">Search</label>
                        <form @submit.prevent="goSearch">
                            <input
                                id="global-search"
                                v-model="search"
                                type="search"
                                placeholder="Search (2+ characters)"
                                class="w-full rounded-pill border border-slate-200 bg-surface px-4 py-2 pr-10 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-brand-light"
                                @focus="showSuggestions = suggestions.length > 0"
                                @blur="hideSuggestionsLater"
                            />
                            <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400" aria-hidden="true">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14Z" />
                                </svg>
                            </span>
                        </form>
                        <div
                            v-if="showSuggestions && suggestions.length"
                            class="absolute z-20 mt-1 w-full rounded-card border border-slate-200 bg-white shadow-card"
                            role="listbox"
                        >
                            <Link
                                v-for="item in suggestions"
                                :key="`${item.object}-${item.id}`"
                                :href="item.url"
                                class="block px-3 py-2 text-sm hover:bg-slate-50"
                                role="option"
                            >
                                <span class="font-medium text-slate-900">{{ item.label }}</span>
                                <span class="ml-2 text-xs uppercase text-slate-400">{{ item.object }}</span>
                                <p v-if="item.subtitle" class="text-xs text-slate-500">{{ item.subtitle }}</p>
                            </Link>
                        </div>
                    </div>

                    <NotificationBell />

                    <div class="relative" data-user-menu>
                        <button
                            type="button"
                            class="flex items-center gap-2 rounded-pill border border-slate-200 bg-white py-1 pl-1 pr-2.5 text-sm transition hover:bg-slate-50"
                            :aria-expanded="userMenuOpen"
                            aria-haspopup="menu"
                            @click.stop="userMenuOpen = !userMenuOpen"
                        >
                            <span class="inline-flex size-8 items-center justify-center rounded-full bg-brand text-xs font-semibold text-white">
                                {{ userInitials }}
                            </span>
                            <span class="hidden max-w-[8rem] truncate font-medium text-slate-800 sm:inline">{{ userName }}</span>
                        </button>

                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 z-30 mt-2 w-52 overflow-hidden rounded-card border border-slate-200 bg-white py-1 shadow-card"
                            role="menu"
                        >
                            <p class="border-b border-slate-100 px-3 py-2 text-xs text-slate-500">
                                {{ page.props.auth.roles?.[0] ?? 'User' }}
                            </p>
                            <Link
                                href="/settings/mfa"
                                class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                                role="menuitem"
                                @click="userMenuOpen = false"
                            >
                                MFA
                            </Link>
                            <Link
                                v-if="canManageWorkflows"
                                href="/admin/workflows"
                                class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                                role="menuitem"
                                @click="userMenuOpen = false"
                            >
                                Workflows
                            </Link>
                            <Link
                                v-if="isAdmin"
                                href="/admin/gdpr"
                                class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                                role="menuitem"
                                @click="userMenuOpen = false"
                            >
                                GDPR
                            </Link>
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                role="menuitem"
                            >
                                Sign out
                            </Link>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <slot />
            </main>
        </div>

        <FlashToast />
    </div>
</template>
