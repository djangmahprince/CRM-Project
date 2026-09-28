<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({ code: '' });

function submit() {
    form.post('/mfa/challenge');
}
</script>

<template>
    <div class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
        <form class="w-full max-w-sm border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="submit">
            <h1 class="text-xl font-semibold text-slate-950">Verify authentication code</h1>
            <p class="mt-2 text-sm text-slate-600">Enter the 6-digit code from your authenticator app.</p>
            <input
                v-model="form.code"
                maxlength="6"
                required
                class="mt-4 w-full border border-slate-300 px-3 py-2 text-sm"
                placeholder="123456"
            />
            <p v-if="form.errors.code" class="mt-2 text-sm text-red-600">{{ form.errors.code }}</p>
            <button type="submit" class="mt-4 w-full bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                Continue
            </button>
        </form>
    </div>
</template>
