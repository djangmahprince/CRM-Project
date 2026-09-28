<script setup>
import { Form, Link } from '@inertiajs/vue3';
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-slate-100 px-6 py-12">
        <section class="w-full max-w-md border border-slate-200 bg-white p-8 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-teal-700">Northstar CRM</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Forgot password</h1>
            <p class="mt-2 text-sm text-slate-600">We will email you a reset link that expires in one hour.</p>

            <Form action="/forgot-password" method="post" class="mt-8 space-y-5" #default="{ errors, processing, recentlySuccessful }">
                <p v-if="recentlySuccessful" class="border border-teal-200 bg-teal-50 px-3 py-2 text-sm text-teal-900">
                    If that email exists, a reset link has been sent.
                </p>

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" required class="mt-2 block w-full border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-100" />
                    <p v-if="errors.email" class="mt-2 text-sm text-red-700">{{ errors.email }}</p>
                </div>

                <button type="submit" :disabled="processing" class="w-full bg-teal-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:opacity-60">
                    {{ processing ? 'Sending...' : 'Email reset link' }}
                </button>
            </Form>

            <Link href="/login" class="mt-6 inline-block text-sm font-medium text-teal-700 hover:text-teal-800">Back to sign in</Link>
        </section>
    </main>
</template>
