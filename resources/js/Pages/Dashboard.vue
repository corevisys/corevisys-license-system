<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        required: false,
        default: () => ({ active_count: 0, expiring_count: 0, total_spent: 0 })
    },
    loading: {
        type: Boolean,
        default: false
    },
    recentLicenses: {
        type: Array,
        default: () => []
    }
});

import { ref, onMounted } from 'vue';

const displayActive = ref(0);
const displayExpiring = ref(0);
const displaySpent = ref(0);

const animateValue = (target, refVar, duration = 1500) => {
    let start = 0;
    const end = parseInt(target);
    if (start === end) return;
    
    const range = end - start;
    let current = start;
    const increment = end > start ? 1 : -1;
    const stepTime = Math.abs(Math.floor(duration / range));
    
    const timer = setInterval(() => {
        current += increment;
        refVar.value = current;
        if (current == end) {
            clearInterval(timer);
        }
    }, Math.max(stepTime, 20));
};

onMounted(() => {
    if (!props.loading) {
        animateValue(props.stats.active_count, displayActive);
        animateValue(props.stats.expiring_count, displayExpiring);
        animateValue(props.stats.total_spent, displaySpent);
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <!-- Hero Section -->
        <div class="mb-12">
            <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">
                Welcome back, <span class="text-brand-blue">{{ $page.props.auth.user.name.split(' ')[0] }}</span>.
            </h2>
            <p v-if="!loading" class="text-text-muted font-medium">You have <span class="text-text-secondary font-semibold">{{ stats.active_count }} active licenses</span> under your account.</p>
            <div v-else class="h-5 bg-slate-100 dark:bg-slate-900 rounded-full w-64 animate-pulse"></div>
        </div>

        <!-- Newly Generated Credentials -->
        <!-- Newly Generated Credentials -->
        <div v-if="$page.props.flash?.new_license_key || $page.props.flash?.new_api_token" 
             class="mb-12 p-8 glass-dark rounded-[40px] border-2 border-brand-teal/30 bg-brand-teal/5 animate-fadeIn relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                <span class="px-3 py-1 bg-brand-teal text-slate-900 text-[8px] font-black uppercase rounded-full">New Purchase</span>
            </div>
            
            <h3 class="text-lg font-black text-adaptive mb-6 uppercase tracking-tight">Access Credentials</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div v-if="$page.props.flash?.new_license_key">
                    <p class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-2">Your New License Key</p>
                    <div class="flex items-center gap-3">
                        <code class="px-4 py-2 bg-black/40 rounded-xl text-brand-teal font-mono text-sm border border-white/5 select-all">{{ $page.props.flash.new_license_key }}</code>
                    </div>
                </div>

                <div v-if="$page.props.flash?.new_api_token">
                    <p class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-2">LMS API Token</p>
                    <div class="flex items-center gap-3">
                        <code class="px-4 py-2 bg-black/40 rounded-xl text-brand-blue font-mono text-sm border border-white/5 select-all">{{ $page.props.flash.new_api_token }}</code>
                    </div>
                </div>
            </div>
            
            <p class="mt-6 text-[10px] text-text-muted italic">Please copy and save these credentials in a secure location.</p>
        </div>

        <!-- KPI Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
            <template v-if="loading">
                <div v-for="i in 3" :key="i" class="glass p-8 rounded-3xl animate-pulse">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-white/5 rounded-2xl"></div>
                        <div class="h-4 bg-white/5 rounded-full w-24"></div>
                    </div>
                    <div class="h-10 bg-white/5 rounded-xl w-16"></div>
                </div>
            </template>
            <template v-else>
                <div class="group glass p-8 rounded-3xl transition-all hover:-translate-y-1 hover:shadow-glow-teal">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand-teal/10 rounded-2xl flex items-center justify-center text-brand-teal group-hover:bg-brand-teal group-hover:text-slate-900 transition-all duration-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <p class="text-sm font-bold text-text-muted uppercase tracking-widest">Active Licenses</p>
                    </div>
                    <div class="flex items-end justify-between">
                        <p class="text-5xl font-black text-adaptive">{{ displayActive }}</p>
                        <Link href="/licenses" class="text-xs font-bold text-brand-teal hover:text-adaptive transition-colors uppercase tracking-widest bg-brand-teal/10 hover:bg-brand-teal px-3 py-1.5 rounded-lg">View All</Link>
                    </div>
                </div>

                <div class="group glass p-8 rounded-3xl transition-all hover:-translate-y-1 hover:shadow-glow-blue border-b-4 border-transparent hover:border-amber-500/20">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-amber-50 dark:bg-amber-900/20 rounded-2xl flex items-center justify-center text-amber-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-sm font-bold text-text-muted uppercase tracking-widest">Expiring Soon</p>
                    </div>
                    <div class="flex items-end justify-between">
                        <p class="text-5xl font-black text-adaptive" :class="{'text-amber-500 animate-pulse': displayExpiring > 0}">{{ displayExpiring }}</p>
                        <span v-if="displayExpiring > 0" class="text-[10px] font-black bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 px-2.5 py-1 rounded-full uppercase tracking-tighter">Action Required</span>
                    </div>
                </div>

                <div class="group glass p-8 rounded-3xl transition-all hover:-translate-y-1 hover:shadow-glow-blue">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 bg-brand-blue/10 rounded-2xl flex items-center justify-center text-brand-blue group-hover:bg-brand-blue group-hover:text-slate-900 transition-all duration-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <p class="text-sm font-bold text-text-muted uppercase tracking-widest">Total Investment</p>
                    </div>
                    <div class="flex items-end justify-between">
                        <p class="text-5xl font-black text-adaptive">${{ displaySpent }}</p>
                        <Link href="/orders" class="text-xs font-bold text-brand-blue hover:text-adaptive transition-colors underline decoration-2 underline-offset-4 bg-brand-blue/10 hover:bg-brand-blue px-3 py-1.5 rounded-lg">History</Link>
                    </div>
                </div>
            </template>
        </div>

        <!-- Section Header -->
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-xl font-black text-adaptive tracking-tight">Recent Activity</h3>
            <button class="text-sm font-bold text-text-muted hover:text-indigo-600 transition-colors flex items-center gap-2 group">
                System Updates
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
        </div>

        <!-- Recent Licenses Section -->
        <div class="bg-bg-dark/50 backdrop-blur-md rounded-[32px] shadow-soft-md border border-white/5 overflow-hidden">
            <div v-if="!recentLicenses || recentLicenses.length === 0" class="p-16 text-center">
                <div class="w-20 h-20 bg-slate-50 dark:bg-slate-900 rounded-3xl flex items-center justify-center mx-auto mb-8 border border-slate-100 dark:border-slate-800">
                    <svg class="w-10 h-10 text-slate-200 dark:text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <h4 class="text-lg font-black text-adaptive mb-2">No licenses found</h4>
                <p class="text-text-muted max-w-sm mx-auto font-medium leading-relaxed">Your recently purchased licenses will appear here for quick access.</p>
            </div>
            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-white/5">
                                <th class="px-8 py-4 text-[10px] font-black text-text-muted uppercase tracking-widest">Product</th>
                                <th class="px-8 py-4 text-[10px] font-black text-text-muted uppercase tracking-widest">License Identifier</th>
                                <th class="px-8 py-4 text-[10px] font-black text-text-muted uppercase tracking-widest">Status</th>
                                <th class="px-8 py-4 text-[10px] font-black text-text-muted uppercase tracking-widest">Expiry</th>
                                <th class="px-8 py-4 text-[10px] font-black text-text-muted uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="license in recentLicenses" :key="license.id" class="group hover:bg-white/5 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 bg-brand-teal/10 rounded-xl flex items-center justify-center text-brand-teal">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                        </div>
                                        <span class="font-bold text-adaptive">{{ license.product.name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <span class="font-mono text-xs text-text-muted">XXXX-XXXX-XXXX-{{ license.license_key_hash.substring(0, 4) }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter',
                                        license.status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                    ]">
                                        {{ license.status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-medium text-text-secondary">
                                    {{ license.expires_at ? new Date(license.expires_at).toLocaleDateString() : 'Lifetime' }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <Link :href="route('licenses')" class="text-[10px] font-black text-brand-teal uppercase tracking-widest hover:underline decoration-2 underline-offset-4">Manage</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
