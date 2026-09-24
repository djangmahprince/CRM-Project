<script setup>
import { Form } from '@inertiajs/vue3';
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-100 px-6 py-12">
        <section class="w-full max-w-md border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">Northstar CRM</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Sign in</h1>
            <p class="mt-2 text-sm text-slate-600">Access your sales and service workspace.</p>

            <Form action="/login" method="post" class="mt-8 space-y-5" #default="{ errors, processing }">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="mt-2 block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100" />
                    <p v-if="errors.email" class="mt-2 text-sm text-red-700">{{ errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-2 block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100" />
                    <p v-if="errors.password" class="mt-2 text-sm text-red-700">{{ errors.password }}</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input name="remember" type="checkbox" value="1" class="size-4 accent-teal-700" />
                    Remember me
                </label>

                <button type="submit" :disabled="processing" class="w-full bg-teal-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-60">
                    {{ processing ? 'Signing in...' : 'Sign in' }}
                </button>
            </Form>
        </section>
    </main>
</template>
