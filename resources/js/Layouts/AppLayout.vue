<script setup>
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
});
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4 lg:px-8">
                <Link href="/dashboard" class="text-lg font-semibold tracking-tight text-slate-950">
                    Northstar CRM
                </Link>
                <nav class="flex items-center gap-5 text-sm font-medium text-slate-600" aria-label="Primary navigation">
                    <Link href="/dashboard" class="transition hover:text-slate-950">Home</Link>
                    <span class="cursor-not-allowed text-slate-300" aria-label="Leads coming soon">Leads</span>
                    <span class="cursor-not-allowed text-slate-300" aria-label="Reports coming soon">Reports</span>
                    <span class="border-l border-slate-200 pl-5 text-slate-500">{{ page.props.auth.user.name }}</span>
                    <Link href="/logout" method="post" as="button" class="transition hover:text-slate-950">Sign out</Link>
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
    </div>
</template>
