<script setup>
import Button from '@/Components/UI/Button.vue';
import InputLabel from '@/Components/UI/InputLabel.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-xl font-black text-text-primary">Update password</h2>
            <p class="mt-1 text-sm text-text-muted">Use a long, random password to keep your account secure.</p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" class="!mb-2">Current password</InputLabel>
                <TextInput id="current_password" ref="currentPasswordInput" v-model="form.current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" class="!mb-2">New password</InputLabel>
                <TextInput id="password" ref="passwordInput" v-model="form.password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" class="!mb-2">Confirm password</InputLabel>
                <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" variant="primary" :disabled="form.processing">Save</Button>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-text-muted">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
