<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

const props = defineProps({
    enabled: Boolean,
    setupSecret: String,
    otpauthUrl: String,
});

const confirmForm = useForm({ code: '' });
const disableForm = useForm({ code: '' });

function enable() {
    confirmForm.post('/settings/mfa');
}

function disable() {
    disableForm.delete('/settings/mfa');
}
</script>

<template>
    <AppLayout title="Multi-factor authentication">
        <section class="max-w-xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-600">
                Status:
                <span class="font-semibold text-slate-950">{{ enabled ? 'Enabled' : 'Disabled' }}</span>
            </p>

            <div v-if="!enabled && setupSecret" class="mt-6 space-y-4">
                <p class="text-sm text-slate-700">
                    Add this secret in your authenticator app, then confirm with a 6-digit code.
                </p>
                <p class="rounded bg-slate-50 px-3 py-2 font-mono text-sm text-slate-900">{{ setupSecret }}</p>
                <p v-if="otpauthUrl" class="break-all text-xs text-slate-500">{{ otpauthUrl }}</p>
                <form class="space-y-3" @submit.prevent="enable">
                    <input v-model="confirmForm.code" maxlength="6" required placeholder="123456" class="w-full border border-slate-300 px-3 py-2 text-sm" />
                    <p v-if="confirmForm.errors.code" class="text-sm text-red-600">{{ confirmForm.errors.code }}</p>
                    <button type="submit" class="bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Enable MFA</button>
                </form>
            </div>

            <form v-else class="mt-6 space-y-3" @submit.prevent="disable">
                <p class="text-sm text-slate-700">Enter a current code to disable MFA.</p>
                <input v-model="disableForm.code" maxlength="6" required placeholder="123456" class="w-full border border-slate-300 px-3 py-2 text-sm" />
                <p v-if="disableForm.errors.code" class="text-sm text-red-600">{{ disableForm.errors.code }}</p>
                <button type="submit" class="border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Disable MFA</button>
            </form>
        </section>
    </AppLayout>
</template>
