<script setup>
defineProps({
    title: {
        type: String,
        default: 'Welcome',
    },
    subtitle: {
        type: String,
        default: 'Sales and service workspace',
    },
});
</script>

<template>
    <div class="min-h-screen bg-surface lg:grid lg:grid-cols-2">
        <aside class="relative hidden overflow-hidden bg-sidebar px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div
                class="pointer-events-none absolute inset-0 opacity-30"
                style="background: radial-gradient(circle at 20% 20%, #5b7dff 0%, transparent 45%), radial-gradient(circle at 80% 80%, #1b3694 0%, transparent 40%);"
                aria-hidden="true"
            />
            <div class="relative">
                <div class="flex items-center gap-3">
                    <svg class="size-9" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                        <path
                            d="M16 2.5 19.2 12.8 29.5 16 19.2 19.2 16 29.5 12.8 19.2 2.5 16 12.8 12.8 16 2.5Z"
                            fill="white"
                        />
                        <circle cx="16" cy="16" r="3.2" fill="#2447b9" />
                    </svg>
                    <p class="text-xl font-semibold tracking-tight">NorthStar</p>
                </div>
                <h1 class="mt-16 max-w-md text-4xl font-semibold leading-tight tracking-tight">
                    {{ title }}
                </h1>
                <p class="mt-4 max-w-sm text-base text-white/75">
                    {{ subtitle }}
                </p>
            </div>
            <p class="relative text-sm text-white/50">Secure access for your team.</p>
        </aside>

        <main class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-2 lg:hidden">
                    <svg class="size-7" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                        <path
                            d="M16 2.5 19.2 12.8 29.5 16 19.2 19.2 16 29.5 12.8 19.2 2.5 16 12.8 12.8 16 2.5Z"
                            fill="#2447b9"
                        />
                        <circle cx="16" cy="16" r="3.2" fill="white" />
                    </svg>
                    <span class="text-lg font-semibold text-slate-950">NorthStar</span>
                </div>
                <div class="ns-card p-8">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>
