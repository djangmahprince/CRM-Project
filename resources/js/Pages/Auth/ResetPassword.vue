<script setup>
import { Form, Link } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

defineProps({
    email: String,
    token: String,
});
</script>

<template>
    <GuestLayout title="Choose a new password" subtitle="Use a strong password you have not used recently.">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Reset password</h2>
        <p class="mt-2 text-sm text-slate-600">Enter and confirm your new password.</p>

        <Form action="/reset-password" method="post" class="mt-8 space-y-5" #default="{ errors, processing }">
            <input type="hidden" name="token" :value="token" />

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" :value="email" required class="ns-input mt-2" />
                <p v-if="errors.email" class="mt-2 text-sm text-danger">{{ errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">New password</label>
                <input id="password" name="password" type="password" required class="ns-input mt-2" />
                <p v-if="errors.password" class="mt-2 text-sm text-danger">{{ errors.password }}</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="ns-input mt-2" />
            </div>

            <button type="submit" :disabled="processing" class="ns-btn-primary w-full py-3">
                {{ processing ? 'Saving...' : 'Reset password' }}
            </button>
        </Form>

        <Link href="/login" class="ns-link mt-6 inline-block text-sm">Back to sign in</Link>
    </GuestLayout>
</template>
