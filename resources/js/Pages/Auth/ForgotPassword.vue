<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import Alert from '@/Components/UI/Alert.vue';
import Button from '@/Components/UI/Button.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/UI/InputLabel.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-6">
            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-amber">Recovery</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-text-primary">Recover your access</h2>
        </div>

        <div class="mb-4 text-sm leading-6 text-text-muted">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
        </div>

        <Alert v-if="status" variant="success" class="mb-4">
            {{ status }}
        </Alert>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" class="!mb-2">Email</InputLabel>

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="pt-2 flex items-center justify-end">
                <Button type="submit" variant="primary" :disabled="form.processing">
                    Email Password Reset Link
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
