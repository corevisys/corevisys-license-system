<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Alert from '@/Components/UI/Alert.vue';
import Button from '@/Components/UI/Button.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-6">
            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-amber">Verification</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-text-primary">Confirm your email</h2>
        </div>

        <div class="mb-4 text-sm leading-6 text-text-muted">
            Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
        </div>

        <Alert v-if="verificationLinkSent" variant="success" class="mb-4">
            A new verification link has been sent to the email address you provided during registration.
        </Alert>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between gap-4">
                <Button type="submit" variant="primary" :disabled="form.processing">
                    Resend Verification Email
                </Button>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-text-muted underline-offset-4 hover:text-amber hover:underline focus:outline-none focus:ring-2 focus:ring-amber/30 focus:ring-offset-2 focus:ring-offset-bg-dark"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
