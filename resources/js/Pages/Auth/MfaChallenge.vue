<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const form = useForm({ code: '' });

function submit() {
    form.post('/mfa/challenge');
}
</script>

<template>
    <GuestLayout title="Verify your identity" subtitle="Enter the 6-digit code from your authenticator app.">
        <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Authentication code</h2>
        <p class="mt-2 text-sm text-slate-600">Complete multi-factor authentication to continue.</p>

        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <div>
                <label for="mfa_code" class="block text-sm font-medium text-slate-700">Code</label>
                <input
                    id="mfa_code"
                    v-model="form.code"
                    maxlength="6"
                    required
                    class="ns-input mt-2"
                    placeholder="123456"
                    autocomplete="one-time-code"
                />
                <p v-if="form.errors.code" class="mt-2 text-sm text-danger">{{ form.errors.code }}</p>
            </div>
            <button type="submit" :disabled="form.processing" class="ns-btn-primary w-full py-3">
                {{ form.processing ? 'Verifying...' : 'Continue' }}
            </button>
        </form>
    </GuestLayout>
</template>
