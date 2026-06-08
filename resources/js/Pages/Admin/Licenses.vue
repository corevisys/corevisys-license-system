<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    licenses: Array
});

const search = ref('');
const statusFilter = ref('all');

const filteredLicenses = computed(() => {
    return props.licenses.filter(license => {
        const matchesStatus = statusFilter.value === 'all' || license.status === statusFilter.value;
        const matchesSearch = 
            license.user_name.toLowerCase().includes(search.value.toLowerCase()) ||
            license.user_email.toLowerCase().includes(search.value.toLowerCase()) ||
            license.product_name.toLowerCase().includes(search.value.toLowerCase());
        
        return matchesStatus && matchesSearch;
    });
});

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

// Polling for Real-Time Updates
import { onMounted, onUnmounted } from 'vue';

let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({ 
            only: ['licenses'],
            preserveScroll: true,
            preserveState: true
        });
    }, 30000); // 30 seconds for admin
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head title="License Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-black text-xl text-adaptive uppercase tracking-widest leading-tight">
                    License <span class="text-brand-teal">Management</span>
                </h2>
                <div class="flex gap-4">
                    <div class="relative group">
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Search user or product..." 
                            class="pl-10 pr-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm text-adaptive placeholder:text-text-muted focus:ring-1 focus:ring-brand-teal/50 transition-all w-64"
                        >
                        <svg class="w-4 h-4 text-text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <!-- Filters -->
            <div class="flex gap-2 mb-6">
                <button 
                    v-for="status in ['all', 'active', 'inactive', 'expired', 'suspended', 'revoked']" 
                    :key="status"
                    @click="statusFilter = status"
                    class="px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest border transition-all"
                    :class="statusFilter === status 
                        ? 'bg-brand-teal text-slate-900 border-brand-teal' 
                        : 'bg-transparent text-text-muted border-white/10 hover:border-brand-teal'"
                >
                    {{ status }}
                </button>
            </div>

            <div class="bg-bg-dark/50 backdrop-blur-xl border border-white/5 rounded-3xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-white/5 bg-white/5">
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">User / Product</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Security Key</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted text-center">Live</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Status / Type</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Expires / Pulse</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="license in filteredLicenses" :key="license.id" class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4">
                                     <div class="flex flex-col">
                                        <span class="text-sm font-bold text-adaptive leading-none mb-1">{{ license.product_name }}</span>
                                        <span class="text-xs text-text-muted">{{ license.user_name }} ({{ license.user_email }})</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <code class="bg-slate-800/50 px-2 py-1 rounded text-brand-teal font-mono text-xs border border-white/5">
                                        {{ license.key_preview }}
                                    </code>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center">
                                        <div 
                                            class="h-2 w-2 rounded-full animate-pulse mr-2" 
                                            :class="license.is_running ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]' : 'bg-slate-700'"
                                        ></div>
                                        <span class="text-[10px] font-bold uppercase tracking-tighter" :class="license.is_running ? 'text-emerald-500' : 'text-slate-600'">
                                            {{ license.is_running ? 'Live' : 'Idle' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <div class="flex flex-col gap-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border w-fit" :class="getStatusColor(license.status)">
                                            {{ license.status }}
                                        </span>
                                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest ml-1">{{ license.type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-xs text-text-muted leading-none mb-1">{{ license.expires_at }}</span>
                                        <span class="text-[9px] text-text-muted/50 uppercase font-black tracking-widest">
                                            Last Pulse: {{ license.last_check_at ? license.last_check_at.split(' ')[1] : 'Never' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link 
                                        :href="route('admin.licenses.show', license.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-teal/10 text-brand-teal border border-brand-teal/20 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-brand-teal hover:text-slate-900 transition-all shadow-lg shadow-brand-teal/5"
                                    >
                                        Details
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="filteredLicenses.length === 0" class="py-20 text-center">
                        <div class="text-text-muted text-sm font-bold uppercase tracking-widest">No licenses found matching your criteria.</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
