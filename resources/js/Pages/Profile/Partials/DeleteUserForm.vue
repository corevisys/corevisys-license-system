<script setup>
import Button from '@/Components/UI/Button.vue';
import InputLabel from '@/Components/UI/InputLabel.vue';
import TextInput from '@/Components/UI/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-xl font-black text-text-primary">Delete account</h2>
            <p class="mt-1 text-sm text-text-muted">Once your account is deleted, all associated resources and data will be permanently removed.</p>
        </header>

        <Button type="button" variant="danger" @click="confirmUserDeletion">Delete account</Button>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-black text-text-primary">Are you sure you want to delete your account?</h2>
                <p class="mt-2 text-sm text-text-muted">Please enter your password to confirm you want to permanently delete the account.</p>

                <div class="mt-6">
                    <InputLabel for="password" class="sr-only">Password</InputLabel>
                    <TextInput id="password" ref="passwordInput" v-model="form.password" type="password" class="mt-1 block w-3/4" placeholder="Password" @keyup.enter="deleteUser" />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <Button type="button" variant="secondary" @click="closeModal">Cancel</Button>
                    <Button type="button" variant="danger" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click="deleteUser">Delete account</Button>
                </div>
            </div>
        </Modal>
    </section>
</template>
