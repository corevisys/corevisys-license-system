<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
        <!-- Page Header -->
        <div class="mb-12 flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">Corporates</h2>
                <p class="text-text-muted font-medium italic">Manage corporate accounts and team-based licensing.</p>
            </div>
            <button class="px-8 py-3 bg-brand-teal text-slate-900 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-brand-teal/20 hover:scale-105 transition-all">
                New Team
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div v-for="team in teams" :key="team.id" class="group bg-bg-dark/50 backdrop-blur-md rounded-[40px] shadow-soft-md border border-white/5 p-8 flex flex-col justify-between transition-all hover:-translate-y-1">
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <div class="w-14 h-14 bg-white/5 rounded-2xl flex items-center justify-center text-text-muted group-hover:text-brand-teal group-hover:bg-white/10 transition-all border border-white/5">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-[10px] font-black text-text-muted bg-white/5 px-3 py-1 rounded-full border border-white/10 uppercase tracking-tighter group-hover:border-brand-teal/20 transition-colors">
                            {{ team.member_count }} Members
                        </span>
                    </div>
                    
                    <h3 class="font-black text-2xl text-adaptive mb-1 tracking-tight">{{ team.name }}</h3>
                    <p class="text-xs text-text-muted mb-6 font-medium italic">Lead by {{ team.owner_name }}</p>
                    
                    <div class="space-y-3 mb-8">
                        <div class="flex items-center gap-3 text-[11px] font-black text-text-muted uppercase tracking-widest">
                            <div class="w-2 h-2 rounded-full bg-brand-teal shadow-lg shadow-brand-teal/40"></div>
                            {{ team.active_licenses }} Active Seats
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button class="flex-1 py-3 bg-white/5 text-text-muted rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all border border-white/5 shadow-sm">
                        Workspace
                    </button>
                    <button class="p-3 bg-rose-50 text-rose-400 hover:text-white hover:bg-rose-500 rounded-2xl transition-all border border-rose-100/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>

            <div v-if="teams.length === 0" class="col-span-full p-20 text-center">
                <div class="w-20 h-20 bg-white/5 rounded-3xl flex items-center justify-center mx-auto mb-8 text-text-muted border border-white/5 opacity-50">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
                <p class="text-xs font-black text-text-muted uppercase tracking-[0.3em]">No Corporate Teams Founded</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
