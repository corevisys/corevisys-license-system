<script setup>
import Card from '@/Components/UI/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = computed(() => usePage().props.auth.user);
const isAdmin = computed(() => user.value.role === 'admin');
const roleLabel = computed(() => isAdmin.value ? 'Admin' : 'Customer');
const statusLabel = computed(() => user.value.status || 'Active');
const memberSince = computed(() => user.value.created_at
    ? new Intl.DateTimeFormat('en', { month: 'short', year: 'numeric' }).format(new Date(user.value.created_at))
    : 'Not available');
const initials = computed(() => user.value.name
    ?.split(' ')
    .map((part) => part[0])
    .join('')
    .slice(0, 2)
    .toUpperCase() || 'CV');
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <div class="-mx-6 min-w-0 space-y-6 sm:mx-0">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-text-primary">Profile</h1>
                    <p class="mt-1 text-sm text-text-muted">Manage your account information, security settings and account details.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                    <span class="rounded-full border border-amber/30 bg-amber/10 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-amber">{{ statusLabel }}</span>
                    <span class="rounded-full border border-panel-line bg-panel-2 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-text-secondary">{{ roleLabel }}</span>
                </div>
            </div>

            <div class="grid min-w-0 gap-6 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.8fr)]">
                <div class="min-w-0 space-y-6">
                    <Card class="min-w-0">
                        <div class="mb-6 flex items-center gap-4 border-b border-panel-line pb-5">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-amber/30 bg-amber/10 text-lg font-black text-amber">{{ initials }}</div>
                            <div class="min-w-0">
                                <p class="truncate text-lg font-bold text-text-primary">{{ user.name }}</p>
                                <p class="truncate text-sm text-text-muted">{{ user.email }}</p>
                            </div>
                        </div>
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </Card>

                    <Card class="min-w-0">
                        <UpdatePasswordForm />
                    </Card>
                </div>

                <div class="min-w-0 space-y-6">
                    <Card class="min-w-0">
                        <section>
                            <header class="mb-5 flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-panel-line bg-panel text-amber">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-8a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-5v6m3-3h-6" /></svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-black text-text-primary">{{ isAdmin ? 'Account Information' : 'Customer Information' }}</h2>
                                    <p class="mt-1 text-sm text-text-muted">{{ isAdmin ? 'Your Corevisys account details.' : 'Your customer account details.' }}</p>
                                </div>
                            </header>

                            <dl class="divide-y divide-panel-line border-y border-panel-line">
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-text-muted">Name</dt>
                                    <dd class="min-w-0 truncate text-right text-sm font-medium text-text-primary">{{ user.name }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-text-muted">Email</dt>
                                    <dd class="min-w-0 max-w-[62%] truncate text-right text-sm font-medium text-text-primary">{{ user.email }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-text-muted">Account type</dt>
                                    <dd class="min-w-0 text-sm font-medium capitalize text-text-primary">{{ roleLabel }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-text-muted">Account status</dt>
                                    <dd class="min-w-0 text-sm font-medium capitalize text-amber">{{ statusLabel }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-sm text-text-muted">Member since</dt>
                                    <dd class="min-w-0 text-sm font-medium text-text-primary">{{ memberSince }}</dd>
                                </div>
                            </dl>
                        </section>
                    </Card>

                    <Card class="min-w-0">
                        <section>
                            <header class="mb-4">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-text-muted">Account summary</p>
                                <h2 class="mt-1 text-lg font-black text-text-primary">Your access at a glance</h2>
                            </header>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-lg border border-panel-line bg-panel p-3">
                                    <p class="text-xs text-text-muted">Status</p>
                                    <p class="mt-1 text-sm font-bold capitalize text-amber">{{ statusLabel }}</p>
                                </div>
                                <div class="rounded-lg border border-panel-line bg-panel p-3">
                                    <p class="text-xs text-text-muted">Role</p>
                                    <p class="mt-1 text-sm font-bold text-text-primary">{{ roleLabel }}</p>
                                </div>
                            </div>
                        </section>
                    </Card>

                    <Card class="min-w-0">
                        <DeleteUserForm />
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
