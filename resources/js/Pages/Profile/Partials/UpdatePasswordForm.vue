<script setup>
import Button from '@/Components/UI/Button.vue';
import InputLabel from '@/Components/UI/InputLabel.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmation = ref(false);

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
        <header class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-panel-line bg-panel text-amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16.5 10.5V7a4.5 4.5 0 0 0-9 0v3.5m-2 0h13a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-13a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Zm6.5 4v3" /></svg>
            </div>
            <div>
                <h2 class="text-xl font-black text-text-primary">Password &amp; Security</h2>
                <p class="mt-1 text-sm text-text-muted">Keep your account secure with a strong password.</p>
            </div>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-5">
            <div>
                <InputLabel for="current_password" class="!mb-2">Current password</InputLabel>
                <div class="relative">
                    <TextInput id="current_password" ref="currentPasswordInput" v-model="form.current_password" :type="showCurrentPassword ? 'text' : 'password'" class="mt-1 block w-full pr-11" autocomplete="current-password" />
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted transition hover:text-amber" :aria-label="showCurrentPassword ? 'Hide current password' : 'Show current password'" @click="showCurrentPassword = !showCurrentPassword">
                        <svg v-if="showCurrentPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.2A10.7 10.7 0 0 1 12 4c5 0 8.5 4 9.5 6-.4.8-1.3 2.1-2.8 3.3M6.2 6.2C4.5 7.4 3.4 8.8 2.5 10c1 2 4.5 6 9.5 6 1 0 1.9-.2 2.7-.5" /></svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Zm9.5 2.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" /></svg>
                    </button>
                </div>
                <InputError :message="form.errors.current_password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password" class="!mb-2">New password</InputLabel>
                <div class="relative">
                    <TextInput id="password" ref="passwordInput" v-model="form.password" :type="showNewPassword ? 'text' : 'password'" class="mt-1 block w-full pr-11" autocomplete="new-password" />
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted transition hover:text-amber" :aria-label="showNewPassword ? 'Hide new password' : 'Show new password'" @click="showNewPassword = !showNewPassword">
                        <svg v-if="showNewPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.2A10.7 10.7 0 0 1 12 4c5 0 8.5 4 9.5 6-.4.8-1.3 2.1-2.8 3.3M6.2 6.2C4.5 7.4 3.4 8.8 2.5 10c1 2 4.5 6 9.5 6 1 0 1.9-.2 2.7-.5" /></svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Zm9.5 2.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" /></svg>
                    </button>
                </div>
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel for="password_confirmation" class="!mb-2">Confirm password</InputLabel>
                <div class="relative">
                    <TextInput id="password_confirmation" v-model="form.password_confirmation" :type="showConfirmation ? 'text' : 'password'" class="mt-1 block w-full pr-11" autocomplete="new-password" />
                    <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-text-muted transition hover:text-amber" :aria-label="showConfirmation ? 'Hide password confirmation' : 'Show password confirmation'" @click="showConfirmation = !showConfirmation">
                        <svg v-if="showConfirmation" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 4.2A10.7 10.7 0 0 1 12 4c5 0 8.5 4 9.5 6-.4.8-1.3 2.1-2.8 3.3M6.2 6.2C4.5 7.4 3.4 8.8 2.5 10c1 2 4.5 6 9.5 6 1 0 1.9-.2 2.7-.5" /></svg>
                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Zm9.5 2.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" /></svg>
                    </button>
                </div>
                <InputError :message="form.errors.password_confirmation" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <Button type="submit" variant="primary" :disabled="form.processing">Update Password</Button>

                <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                    <p v-if="form.recentlySuccessful" class="text-sm text-text-muted">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>
