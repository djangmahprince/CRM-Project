<script setup>
import { Form, Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import GuestLayout from '../../Layouts/GuestLayout.vue';

const page = usePage();

const props = defineProps({
    tab: {
        type: String,
        default: 'login',
    },
});

const activeTab = ref(props.tab === 'register' ? 'register' : 'login');

watch(
    () => props.tab,
    (value) => {
        activeTab.value = value === 'register' ? 'register' : 'login';
    },
);

const hasRegisterErrors = computed(() => {
    const errors = page.props.errors || {};
    return ['name', 'email', 'password', 'password_confirmation', 'invitation_code']
        .some((key) => Boolean(errors[key]));
});

watch(hasRegisterErrors, (value) => {
    if (value) {
        activeTab.value = 'register';
    }
}, { immediate: true });
</script>

<template>
    <GuestLayout
        :title="activeTab === 'register' ? 'Create your account' : 'Welcome back'"
        :subtitle="activeTab === 'register'
            ? 'Use your invitation code to join the workspace.'
            : 'Sign in to your sales and service workspace.'"
    >
        <div class="mb-6 flex rounded-pill bg-surface p-1">
            <button
                type="button"
                class="flex-1 rounded-pill px-3 py-2 text-sm font-semibold transition"
                :class="activeTab === 'login' ? 'bg-white text-brand shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                @click="activeTab = 'login'"
            >
                Login
            </button>
            <button
                type="button"
                class="flex-1 rounded-pill px-3 py-2 text-sm font-semibold transition"
                :class="activeTab === 'register' ? 'bg-white text-brand shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                @click="activeTab = 'register'"
            >
                Create Account
            </button>
        </div>

        <Form
            v-if="activeTab === 'login'"
            action="/login"
            method="post"
            class="space-y-5"
            #default="{ errors, processing }"
        >
            <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Sign in</h2>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" autocomplete="email" required class="ns-input mt-2" />
                <p v-if="errors.email" class="mt-2 text-sm text-danger">{{ errors.email }}</p>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="ns-input mt-2" />
                <p v-if="errors.password" class="mt-2 text-sm text-danger">{{ errors.password }}</p>
            </div>

            <div class="flex items-center justify-between gap-4">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input name="remember" type="checkbox" value="1" class="size-4 accent-brand" />
                    Remember me
                </label>
                <Link href="/forgot-password" class="ns-link text-sm">Forgot password?</Link>
            </div>

            <button type="submit" :disabled="processing" class="ns-btn-primary w-full py-3">
                {{ processing ? 'Signing in...' : 'Sign in' }}
            </button>
        </Form>

        <Form
            v-else
            action="/register"
            method="post"
            class="space-y-5"
            #default="{ errors, processing }"
        >
            <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Create Account</h2>
            <p class="text-sm text-slate-600">Registration requires a valid invitation code.</p>

            <div>
                <label for="register_name" class="block text-sm font-medium text-slate-700">Name</label>
                <input id="register_name" name="name" type="text" autocomplete="name" required class="ns-input mt-2" />
                <p v-if="errors.name" class="mt-2 text-sm text-danger">{{ errors.name }}</p>
            </div>

            <div>
                <label for="register_email" class="block text-sm font-medium text-slate-700">Email</label>
                <input id="register_email" name="email" type="email" autocomplete="email" required class="ns-input mt-2" />
                <p v-if="errors.email" class="mt-2 text-sm text-danger">{{ errors.email }}</p>
            </div>

            <div>
                <label for="register_password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="register_password" name="password" type="password" autocomplete="new-password" required class="ns-input mt-2" />
                <p v-if="errors.password" class="mt-2 text-sm text-danger">{{ errors.password }}</p>
            </div>

            <div>
                <label for="register_password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
                <input id="register_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="ns-input mt-2" />
            </div>

            <div>
                <label for="invitation_code" class="block text-sm font-medium text-slate-700">Invitation code</label>
                <input id="invitation_code" name="invitation_code" type="text" autocomplete="off" required class="ns-input mt-2" />
                <p v-if="errors.invitation_code" class="mt-2 text-sm text-danger">{{ errors.invitation_code }}</p>
            </div>

            <button type="submit" :disabled="processing" class="ns-btn-primary w-full py-3">
                {{ processing ? 'Creating account...' : 'Create account' }}
            </button>
        </Form>
    </GuestLayout>
</template>
