<script setup>
import { Form, Link } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
</script>

<template>
    <GuestLayout title="Reset your password" subtitle="We will email you a reset link that expires in one hour.">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Forgot password</h2>
        <p class="mt-2 text-sm text-slate-600">Enter the email associated with your account.</p>

        <Form action="/forgot-password" method="post" class="mt-8 space-y-5" #default="{ errors, processing, recentlySuccessful }">
            <p v-if="recentlySuccessful" class="rounded-lg border border-brand-light bg-brand-light px-3 py-2 text-sm text-brand-dark">
                If that email exists, a reset link has been sent.
            </p>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" required class="ns-input mt-2" />
                <p v-if="errors.email" class="mt-2 text-sm text-danger">{{ errors.email }}</p>
            </div>

            <button type="submit" :disabled="processing" class="ns-btn-primary w-full py-3">
                {{ processing ? 'Sending...' : 'Email reset link' }}
            </button>
        </Form>

        <Link href="/login" class="ns-link mt-6 inline-block text-sm">Back to sign in</Link>
    </GuestLayout>
</template>
