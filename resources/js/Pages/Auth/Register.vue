<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/UI/InputLabel.vue';
import Button from '@/Components/UI/Button.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-6">
            <p class="text-[11px] font-black uppercase tracking-[0.24em] text-amber">Create account</p>
            <h2 class="mt-2 text-2xl font-black tracking-tight text-text-primary">Join CoreVisys</h2>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" class="!mb-2">Name</InputLabel>

                <TextInput
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" class="!mb-2">Email</InputLabel>

                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" class="!mb-2">Password</InputLabel>

                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" class="!mb-2">Confirm Password</InputLabel>

                <TextInput
                    id="password_confirmation"
                    type="password"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <Link
                    :href="route('login')"
                    class="text-sm text-text-muted underline-offset-4 hover:text-amber hover:underline focus:outline-none focus:ring-2 focus:ring-amber/30 focus:ring-offset-2 focus:ring-offset-bg-dark"
                >
                    Already registered?
                </Link>

                <Button type="submit" variant="primary" :disabled="form.processing">
                    Register
                </Button>
            </div>
        </form>
    </GuestLayout>
</template>
