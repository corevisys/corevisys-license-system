<script setup>
import Alert from '@/Components/UI/Alert.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import InputLabel from '@/Components/UI/InputLabel.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import TerminalBlock from '@/Components/UI/TerminalBlock.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { useTheme } from '@/Composables/useTheme';
import { ArrowRight, Fingerprint, KeyRound, ShieldCheck, Sparkles, TerminalSquare } from 'lucide-vue-next';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onBeforeMount, ref } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { initTheme } = useTheme();

onBeforeMount(() => {
    initTheme();
});

const touched = ref({
    email: false,
    password: false,
});

const submitMessage = ref('');

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const emailError = computed(() => {
    if (form.errors.email) {
        return form.errors.email;
    }

    if (!touched.value.email) {
        return '';
    }

    if (!form.email.trim()) {
        return 'Email is required.';
    }

    const isValidEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email);

    if (!isValidEmail) {
        return 'Enter a valid email address.';
    }

    return '';
});

const passwordError = computed(() => {
    if (form.errors.password) {
        return form.errors.password;
    }

    if (!touched.value.password) {
        return '';
    }

    if (!form.password) {
        return 'Password is required.';
    }

    if (form.password.length < 8) {
        return 'Password must be at least 8 characters.';
    }

    return '';
});

const alertVariant = computed(() => {
    if (props.status) return 'success';
    if (!submitMessage.value) return 'default';
    return submitMessage.value.includes('Invalid') || submitMessage.value.includes('Please correct') ? 'danger' : 'default';
});

const validateField = (field) => {
    touched.value[field] = true;
};

const submit = () => {
    touched.value.email = true;
    touched.value.password = true;

    if (emailError.value || passwordError.value) {
        submitMessage.value = 'Please correct the highlighted fields and try again.';
        return;
    }

    submitMessage.value = 'Authenticating your session...';

    form.post(route('login'), {
        preserveScroll: true,
        onSuccess: () => {
            submitMessage.value = 'Signed in successfully. Redirecting...';
        },
        onError: () => {
            submitMessage.value = 'Invalid credentials. Please check your email and password.';
        },
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-bg-dark text-text-primary">
        <Head title="Log in" />

        <div class="mx-auto flex min-h-screen max-w-7xl items-center justify-center p-4 sm:p-8">
            <div class="grid w-full overflow-hidden rounded-[32px] border border-panel-line bg-panel/80 shadow-soft-md lg:grid-cols-[1.15fr_0.85fr]">
                <section class="relative flex flex-col justify-between overflow-hidden border-b border-panel-line bg-[radial-gradient(circle_at_top_left,_rgba(232,163,61,0.14),_transparent_35%),linear-gradient(135deg,rgba(23,20,15,0.9),rgba(16,14,12,0.94))] p-6 sm:p-8 lg:border-b-0 lg:border-r">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,_rgba(79,214,192,0.08),_transparent_20%)]" aria-hidden="true" />

                    <div class="relative z-10">
                        <Badge status="success" class="!rounded-full px-3 py-1.5">
                            System healthy
                        </Badge>

                        <div class="mt-8 max-w-lg">
                            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-amber">CoreVisys control plane</p>
                            <h1 class="mt-4 text-3xl font-black tracking-tight text-text-primary sm:text-4xl">Access your secure workspace</h1>
                            <p class="mt-3 text-sm leading-6 text-text-muted">
                                Sign in to manage license activation, deployment health, billing, and operational workflows in one place.
                            </p>
                        </div>

                        <div class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <div class="flex items-center gap-3 text-amber">
                                    <ShieldCheck class="h-4 w-4" />
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em]">License health</span>
                                </div>
                                <p class="mt-4 text-2xl font-black text-text-primary">99.94%</p>
                                <p class="mt-1 text-xs text-text-muted">Active and compliant</p>
                            </div>

                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <div class="flex items-center gap-3 text-teal">
                                    <Fingerprint class="h-4 w-4" />
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em]">Session</span>
                                </div>
                                <p class="mt-4 text-2xl font-black text-text-primary">12.4k</p>
                                <p class="mt-1 text-xs text-text-muted">Protected requests</p>
                            </div>
                        </div>
                    </div>

                    <TerminalBlock class="relative z-10 mt-8">
                        <div class="flex items-center justify-between border-b border-panel-line pb-3">
                            <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">
                                <TerminalSquare class="h-3.5 w-3.5 text-amber" />
                                system status
                            </div>
                            <Badge status="success" class="!px-2 !py-1 !text-[10px]">online</Badge>
                        </div>

                        <div class="mt-4 space-y-3 font-mono text-[11px] leading-6 text-text-muted">
                            <div class="flex items-center justify-between">
                                <span>tenant</span>
                                <span class="text-text-primary">corevisys-ops</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>license</span>
                                <span class="text-teal">active</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>last sync</span>
                                <span class="text-text-primary">02:41 UTC</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>support</span>
                                <span class="text-amber">priority</span>
                            </div>
                        </div>
                    </TerminalBlock>
                </section>

                <section class="flex items-center justify-center bg-bg-dark/60 p-5 sm:p-8 lg:p-10">
                    <div class="w-full max-w-md">
                        <div class="mb-7 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber text-[#1A1305] shadow-lg shadow-amber/20">
                                    <KeyRound class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Access portal</p>
                                    <p class="text-lg font-black text-text-primary">CoreVisys</p>
                                </div>
                            </div>
                            <Badge status="default" class="!rounded-full px-2.5 py-1 !text-[10px]">
                                <Sparkles class="h-3 w-3 text-teal" />
                                Secure
                            </Badge>
                        </div>

                        <div class="mb-6">
                            <h2 class="text-3xl font-black tracking-tight text-text-primary">Welcome back</h2>
                            <p class="mt-2 text-sm text-text-muted">Sign in to continue to your workspace and operational dashboard.</p>
                        </div>

                        <Alert v-if="status || submitMessage" :variant="alertVariant">
                            {{ status || submitMessage }}
                        </Alert>

                        <form @submit.prevent="submit" novalidate class="mt-5 space-y-5">
                            <div>
                                <InputLabel for="email" class="!mb-2">Work email</InputLabel>

                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="username"
                                    autofocus
                                    :class="emailError ? 'border-danger/50 bg-danger/5 focus:ring-danger/20' : ''"
                                    :aria-invalid="!!emailError"
                                    :aria-describedby="emailError ? 'email-error' : undefined"
                                    @blur="validateField('email')"
                                />

                                <InputError id="email-error" class="mt-2" :message="emailError" />
                            </div>

                            <div>
                                <div class="flex items-center justify-between gap-3">
                                    <InputLabel for="password" class="!mb-2">Password</InputLabel>
                                    <Link
                                        v-if="canResetPassword"
                                        :href="route('password.request')"
                                        class="text-xs font-medium text-amber underline-offset-4 hover:underline focus:outline-none focus:ring-2 focus:ring-amber/30 focus:ring-offset-2 focus:ring-offset-bg-dark"
                                    >
                                        Forgot password?
                                    </Link>
                                </div>

                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    autocomplete="current-password"
                                    :class="passwordError ? 'border-danger/50 bg-danger/5 focus:ring-danger/20' : ''"
                                    :aria-invalid="!!passwordError"
                                    :aria-describedby="passwordError ? 'password-error' : undefined"
                                    @blur="validateField('password')"
                                />

                                <InputError id="password-error" class="mt-2" :message="passwordError" />
                            </div>

                            <div class="flex items-center justify-between gap-3 pt-1">
                                <label class="flex items-center gap-3 text-sm text-text-muted">
                                    <Checkbox name="remember" v-model:checked="form.remember" class="rounded-md border-panel-line bg-panel-2 text-amber focus:ring-amber/30" />
                                    <span>Remember me</span>
                                </label>
                            </div>

                            <div class="pt-2">
                                <Button type="submit" variant="primary" class="w-full justify-center" :disabled="form.processing">
                                    <template #icon-after>
                                        <ArrowRight class="h-4 w-4" />
                                    </template>
                                    Sign in
                                </Button>
                            </div>
                        </form>

                        <div class="mt-6 flex items-center gap-3">
                            <div class="h-px flex-1 bg-panel-line" />
                            <span class="text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">or continue with</span>
                            <div class="h-px flex-1 bg-panel-line" />
                        </div>

                        <Button type="button" variant="secondary" class="mt-5 w-full justify-center">
                            <template #icon-before>
                                <Fingerprint class="h-4 w-4" />
                            </template>
                            Workspace SSO
                        </Button>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
