<script setup>
import Alert from '@/Components/UI/Alert.vue';
import Button from '@/Components/UI/Button.vue';
import InputLabel from '@/Components/UI/InputLabel.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-black text-text-primary">Profile Information</h2>
            <p class="mt-1 text-sm text-text-muted">Manage your personal account information.</p>
        </header>

        <form @submit.prevent="form.patch(route('profile.update'))" class="mt-6 space-y-5">
            <div>
                <InputLabel for="name" class="!mb-2">Full name</InputLabel>
                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus autocomplete="name" />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" class="!mb-2">Email address</InputLabel>
                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-text-muted">
                    Your email address is unverified.
                    <Link :href="route('verification.send')" method="post" as="button" class="rounded-md text-sm text-amber underline-offset-4 hover:underline">
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <Alert v-show="status === 'verification-link-sent'" variant="success" class="mt-3">
                    A new verification link has been sent to your email address.
                </Alert>
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" variant="primary" :disabled="form.processing">Save Changes</Button>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-text-muted">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
