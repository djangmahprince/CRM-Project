<script setup>
import { Form, Link } from '@inertiajs/vue3';

defineProps({
    email: String,
    token: String,
});
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-100 px-6 py-12">
        <section class="w-full max-w-md border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">Northstar CRM</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Reset password</h1>
            <p class="mt-2 text-sm text-slate-600">Use a strong password you have not used recently.</p>

            <Form action="/reset-password" method="post" class="mt-8 space-y-5" #default="{ errors, processing }">
                <input type="hidden" name="token" :value="token" />

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" :value="email" required class="mt-2 block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100" />
                    <p v-if="errors.email" class="mt-2 text-sm text-red-700">{{ errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">New password</label>
                    <input id="password" name="password" type="password" required class="mt-2 block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100" />
                    <p v-if="errors.password" class="mt-2 text-sm text-red-700">{{ errors.password }}</p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-2 block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100" />
                </div>

                <button type="submit" :disabled="processing" class="w-full bg-teal-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:opacity-60">
                    {{ processing ? 'Saving...' : 'Reset password' }}
                </button>
            </Form>

            <Link href="/login" class="mt-6 inline-block text-sm font-medium text-teal-700 hover:text-teal-800">Back to sign in</Link>
        </section>
    </main>
</template>
