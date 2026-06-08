<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    license: Object
});

const updateStatus = (status) => {
    const msg = status === 'revoked' 
        ? 'WARNING: This is KILL MODE. The project will wipe all local license data and shutdown immediately. Proceed?'
        : `Are you sure you want to set this license to ${status}?`;
        
    if (confirm(msg)) {
        router.post(route('admin.licenses.status', props.license.id), { status });
    }
};

const resetBinding = () => {
    if (confirm('Are you sure you want to reset bindings for this license? The user will need to reactivate on their next run.')) {
        router.post(route('admin.licenses.reset-binding', props.license.id));
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'active': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
        case 'inactive': return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
        case 'expired': return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
        case 'suspended': return 'bg-rose-500/10 text-rose-500 border-rose-500/20';
        case 'revoked': return 'bg-red-600/10 text-red-500 border-red-500/20';
        default: return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
    }
};
</script>

<template>
    <Head :title="'License Details - ' + license.product_name" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('admin.licenses')" class="p-2 hover:bg-white/10 rounded-xl text-text-muted transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                </Link>
                <h2 class="font-black text-xl text-adaptive uppercase tracking-widest leading-tight">
                    License <span class="text-brand-teal">Details</span>
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: License Info -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Overview Card -->
                    <div class="bg-bg-dark/50 backdrop-blur-xl border border-white/5 rounded-3xl p-8 shadow-2xl">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h3 class="text-2xl font-black text-adaptive mb-2">{{ license.product_name }}</h3>
                                <p class="text-text-muted text-sm font-bold uppercase tracking-widest">{{ license.type }} Edition</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <div 
                                    class="flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                    :class="license.is_running ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20 shadow-[0_0_12px_rgba(16,185,129,0.2)]' : 'bg-slate-500/10 text-slate-500 border-slate-500/20'"
                                >
                                    <div class="h-1.5 w-1.5 rounded-full" :class="license.is_running ? 'bg-emerald-500 animate-pulse' : 'bg-slate-500'"></div>
                                    {{ license.is_running ? 'Live' : 'Idle' }}
                                </div>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border" :class="getStatusColor(license.status)">
                                    {{ license.status }}
                                </span>
                            </div>
                        </div>

                        <div class="mb-8 p-4 bg-white/5 rounded-2xl border border-white/5 flex items-center justify-between">
                            <div class="flex flex-col">
                                <span class="text-[9px] font-black text-text-muted uppercase tracking-widest mb-1">Last Heartbeat Link</span>
                                <span class="text-adaptive font-bold flex items-center gap-2 text-xs">
                                    <svg class="w-3.5 h-3.5 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    {{ license.last_check_at || 'Never established' }}
                                </span>
                            </div>
                            <div v-if="license.is_running" class="text-[9px] font-black text-emerald-500 uppercase tracking-widest animate-pulse">
                                Verified Connection Active
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-2 block">License Key</label>
                                    <code class="block bg-bg-dark/80 px-4 py-3 rounded-2xl text-brand-teal font-mono text-sm border border-white/5 break-all">
                                        {{ license.license_key }}
                                    </code>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-2 block">Valid Until</label>
                                    <p class="text-adaptive font-bold">{{ license.expires_at }}</p>
                                </div>
                            </div>
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-2 block">Assigned User</label>
                                    <div class="flex flex-col">
                                        <span class="text-adaptive font-bold">{{ license.user_name }}</span>
                                        <span class="text-text-muted text-xs">{{ license.user_email }}</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-2 block">Enforcement Mode</label>
                                    <p class="text-adaptive font-bold uppercase tracking-widest text-xs">{{ license.enforcement_mode }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Management Actions -->
                    <div class="bg-bg-dark/50 backdrop-blur-xl border border-white/5 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-adaptive font-black uppercase tracking-widest mb-6 flex items-center gap-2">
                             <div class="w-1.5 h-1.5 rounded-full bg-brand-teal"></div>
                             Management Actions
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <button 
                                v-if="license.status !== 'active'"
                                @click="updateStatus('active')"
                                class="flex items-center justify-between p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl hover:bg-emerald-500 hover:text-slate-900 transition-all group"
                            >
                                <div class="text-left">
                                    <div class="text-xs font-black uppercase tracking-widest">Activate</div>
                                    <div class="text-[10px] text-emerald-500/70 group-hover:text-slate-900/70">Restore full access to this license</div>
                                </div>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </button>

                            <button 
                                v-if="license.status !== 'suspended'"
                                @click="updateStatus('suspended')"
                                class="flex items-center justify-between p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl hover:bg-rose-500 hover:text-white transition-all group"
                            >
                                <div class="text-left">
                                    <div class="text-xs font-black uppercase tracking-widest">Suspend</div>
                                    <div class="text-[10px] text-rose-500/70 group-hover:text-white/70">Temporarily block application usage</div>
                                </div>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            </button>

                            <button 
                                v-if="license.status !== 'revoked'"
                                @click="updateStatus('revoked')"
                                class="flex items-center justify-between p-4 bg-red-600/10 border border-red-600/20 rounded-2xl hover:bg-red-600 hover:text-white transition-all group"
                            >
                                <div class="text-left">
                                    <div class="text-xs font-black uppercase tracking-widest">Revoke (Kill Mode)</div>
                                    <div class="text-[10px] text-red-500/70 group-hover:text-white/70">Wipe local data & permanent shutdown</div>
                                </div>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </button>

                            <button 
                                v-if="license.bound_domain || license.bound_ip"
                                @click="resetBinding"
                                class="flex items-center justify-between p-4 bg-white/5 border border-white/5 rounded-2xl hover:bg-white hover:text-slate-900 transition-all group"
                            >
                                <div class="text-left">
                                    <div class="text-xs font-black uppercase tracking-widest">Reset Bindings</div>
                                    <div class="text-[10px] text-text-muted group-hover:text-slate-700">Clear domain/IP and allow new activation</div>
                                </div>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Binding & History -->
                <div class="space-y-8">
                    <!-- Binding Details -->
                    <div class="bg-bg-dark/50 backdrop-blur-xl border border-white/5 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-sm font-black text-adaptive mb-6 uppercase tracking-widest flex items-center gap-2">
                             <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                             Environment Binding
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                                <label class="text-[9px] font-black text-text-muted uppercase tracking-widest mb-1 block">Bound Domain</label>
                                <p class="text-sm font-mono text-adaptive">{{ license.bound_domain || 'NOT BOUND' }}</p>
                            </div>
                            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                                <label class="text-[9px] font-black text-text-muted uppercase tracking-widest mb-1 block">Bound IP Address</label>
                                <p class="text-sm font-mono text-adaptive">{{ license.bound_ip || 'NOT BOUND' }}</p>
                            </div>
                            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                                <label class="text-[9px] font-black text-text-muted uppercase tracking-widest mb-1 block">Activation Limit</label>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-black text-adaptive">{{ license.current_usage }} / {{ license.activation_limit }}</p>
                                    <div class="flex-1 ml-4 h-1 bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-teal transition-all duration-1000" :style="{ width: (license.activation_limit > 0 ? (license.current_usage / license.activation_limit * 100) : 0) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick History Snippet -->
                    <div class="bg-bg-dark/50 backdrop-blur-xl border border-white/5 rounded-3xl p-8 shadow-2xl">
                        <h3 class="text-sm font-black text-adaptive mb-6 uppercase tracking-widest flex items-center gap-2">
                             <svg class="w-4 h-4 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                             Recent Pulse Activity
                        </h3>
                        
                        <div class="space-y-4">
                            <div v-for="log in license.history.slice(0, 5)" :key="log.id" class="flex gap-4 items-start p-3 bg-white/5 rounded-xl border border-white/5">
                                <div class="w-1.5 h-1.5 rounded-full mt-1.5 shrink-0" :class="log.status === 'pulse' ? 'bg-brand-teal' : 'bg-brand-teal/50'"></div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[10px] font-black text-adaptive uppercase tracking-tighter">{{ log.status }}</span>
                                        <span class="text-[9px] text-text-muted">{{ log.created_at }}</span>
                                    </div>
                                    <div class="text-[10px] text-text-muted font-mono">{{ log.request_domain }}</div>
                                </div>
                            </div>
                            <div v-if="license.history.length === 0" class="text-center py-4 italic text-text-muted text-xs">
                                No activity recorded.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full History Table -->
            <div class="mt-8 bg-bg-dark/50 backdrop-blur-xl border border-white/5 rounded-3xl overflow-hidden shadow-2xl">
                <div class="p-6 border-b border-white/5">
                    <h3 class="text-adaptive font-black uppercase tracking-widest text-sm">Detailed Verification Log</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white/5">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Timestamp</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Domain</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">IP Address</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Event Type</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-xs text-text-muted">
                            <tr v-for="log in license.history" :key="log.id" class="hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4">{{ log.created_at }}</td>
                                <td class="px-6 py-4 font-mono">{{ log.request_domain }}</td>
                                <td class="px-6 py-4 font-mono">{{ log.request_ip }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-tighter border"
                                        :class="log.status === 'pulse' ? 'border-brand-teal/30 text-brand-teal' : 'border-emerald-500/30 text-emerald-500'"
                                    >
                                        {{ log.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
