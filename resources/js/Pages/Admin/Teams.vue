<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    teams: {
        type: Array,
        required: true,
    }
});
</script>

<template>
    <Head title="Team Management" />

    <AuthenticatedLayout>
        <div class="mb-12 flex items-center justify-between gap-4">
            <div>
                <Badge status="success" class="!rounded-full px-3 py-1.5">Corporate roster</Badge>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">Corporates</h2>
                <p class="mt-2 text-sm text-text-muted">Manage corporate accounts and team-based licensing.</p>
            </div>
            <Button type="button" variant="primary">New team</Button>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
            <Card v-for="team in teams" :key="team.id" class="flex min-h-[260px] flex-col justify-between p-6">
                <div>
                    <div class="mb-6 flex items-center justify-between">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-panel-line bg-panel-2 text-text-secondary">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <Badge status="default">{{ team.member_count }} members</Badge>
                    </div>

                    <h3 class="mb-1 text-2xl font-black tracking-tight text-text-primary">{{ team.name }}</h3>
                    <p class="mb-6 text-xs font-medium italic text-text-muted">Lead by {{ team.owner_name }}</p>

                    <div class="mb-8 flex items-center gap-3 text-[11px] font-black uppercase tracking-[0.2em] text-text-muted">
                        <span class="h-2 w-2 rounded-full bg-teal" />
                        {{ team.active_licenses }} active seats
                    </div>
                </div>

                <div class="flex gap-3">
                    <Button type="button" variant="secondary" class="flex-1">Workspace</Button>
                    <Button type="button" variant="secondary" class="px-3">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </Button>
                </div>
            </Card>

            <div v-if="teams.length === 0" class="col-span-full rounded-[24px] border border-panel-line bg-panel-2 p-20 text-center">
                <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl border border-panel-line bg-panel-2 text-text-muted opacity-60">
                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <p class="text-xs font-black uppercase tracking-[0.3em] text-text-muted">No corporate teams founded</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
