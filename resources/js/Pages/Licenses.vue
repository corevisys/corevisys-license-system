<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue'; 
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    licenses: {
        type: Array,
        required: false,
        default: () => []
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const copyKey = (key) => {
    if (!key) return;
    navigator.clipboard.writeText(key);
    alert('License key copied to clipboard!');
};

const managingLicense = ref(null);
const upgradingLicense = ref(null);

const handleUpgrade = (license, priceId) => {
    router.post(route('licenses.upgrade', license.id), {
        product_price_id: priceId
    });
};

const handleRenew = (license) => {
    if (confirm('Proceed to renewal for ' + license.product_name + '?')) {
        router.post(route('licenses.renew', license.id));
    }
};

const openConfig = (license) => {
    router.get(route('licenses.config', license.id));
};

// Polling for Real-Time Updates
import { onMounted, onUnmounted } from 'vue';

let pollInterval = null;

onMounted(() => {
    // Poll every 60 seconds to refresh license data (only the licenses prop)
    pollInterval = setInterval(() => {
        router.reload({ 
            only: ['licenses'],
            preserveScroll: true,
            preserveState: true
        });
    }, 60000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head title="My Licenses" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="mb-12 flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">My Assets</h2>
                <p class="text-text-muted font-medium italic">Manage your active software seats and domain bindings.</p>
            </div>
            <button class="px-8 py-3 bg-brand-teal text-slate-900 rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg shadow-brand-teal/20 hover:scale-105 transition-all">
                New License
            </button>
        </div>

        <!-- Table Container -->
        <div class="bg-bg-dark/50 backdrop-blur-md rounded-[40px] shadow-soft-md border border-white/5 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-transparent">
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Product</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Security Key</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Health</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Environment</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Valid Until</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <template v-if="loading">
                        <tr v-for="i in 5" :key="i" class="animate-pulse">
                            <td class="px-8 py-6"><div class="h-4 bg-white/5 rounded-full w-32 mb-2"></div><div class="h-3 bg-white/5 rounded-full w-20"></div></td>
                            <td class="px-8 py-6"><div class="h-8 bg-white/5 rounded-xl w-48"></div></td>
                            <td class="px-8 py-6"><div class="h-5 bg-white/5 rounded-full w-16"></div></td>
                            <td class="px-8 py-6"><div class="h-4 bg-white/5 rounded-full w-24"></div></td>
                            <td class="px-8 py-6 text-right"><div class="h-8 bg-white/5 rounded-xl w-16 ml-auto"></div></td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr v-for="license in licenses" :key="license.id" class="group hover:bg-white/5 transition-all cursor-default">
                             <td class="px-8 py-6">
                                <div class="font-black text-adaptive leading-tight mb-0.5">{{ license.product_name }}</div>
                                <div class="text-[10px] font-bold text-text-muted uppercase tracking-widest">{{ license.type }} Edition</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <code class="bg-bg-dark px-3 py-1.5 rounded-xl text-brand-teal font-mono text-sm border border-white/10">
                                        {{ license.key_preview }}
                                    </code>
                                    <button @click="copyKey(license.full_key)" class="p-2 hover:bg-white/5 rounded-xl text-text-muted hover:text-brand-teal transition-all shadow-sm border border-transparent hover:border-white/10" title="Secure Copy">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex flex-col gap-1.5">
                                        <span :class="[
                                            'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter w-fit',
                                            license.status === 'active' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400'
                                        ]">
                                            {{ license.status }}
                                        </span>
                                        <div class="flex items-center ml-1">
                                            <div 
                                                class="h-1.5 w-1.5 rounded-full mr-2" 
                                                :class="license.is_running ? 'bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]' : 'bg-slate-700'"
                                            ></div>
                                            <span class="text-[9px] font-bold uppercase tracking-widest leading-none" :class="license.is_running ? 'text-emerald-500' : 'text-slate-500'">
                                                {{ license.is_running ? 'Live Now' : 'Idle' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-sm font-bold text-adaptive leading-tight">
                                    {{ license.bound_domain || 'Not Bound' }}
                                </div>
                                <div class="text-[10px] font-bold text-text-muted font-mono tracking-widest mt-0.5">
                                    {{ license.bound_ip || '---' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="text-sm font-bold tracking-tight" :class="license.is_expiring_soon ? 'text-amber-500' : 'text-text-muted'">
                                    {{ license.expires_at || 'Lifetime' }}
                                </div>
                                <div v-if="license.is_expiring_soon" class="text-[9px] font-black text-amber-600 uppercase tracking-tighter">Expiring Soon</div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click="openConfig(license)" class="px-4 py-2 bg-white/5 text-text-muted rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all border border-white/10">
                                        Config
                                    </button>
                                    <button v-if="license.upgrade_plans && license.upgrade_plans.length > 0" @click="upgradingLicense = license" class="px-5 py-2.5 bg-brand-teal/10 text-brand-teal text-xs font-black uppercase tracking-widest rounded-xl hover:bg-brand-teal hover:text-slate-900 transition-all border border-brand-teal/20">
                                        Upgrade
                                    </button>
                                    <button v-if="license.can_renew" @click="handleRenew(license)" class="px-5 py-2.5 bg-brand-teal text-slate-900 text-xs font-black uppercase tracking-widest rounded-xl hover:scale-105 transition-all shadow-md shadow-brand-teal/10">
                                        Renew
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div v-if="licenses.length === 0" class="p-20 text-center">
                <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-6 text-text-muted">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                </div>
                <p class="text-sm font-bold text-text-muted uppercase tracking-[0.2em]">Secure Vault is Empty</p>
            </div>
        </div>

        <!-- Upgrade Modal -->
        <Modal :show="!!upgradingLicense" @close="upgradingLicense = null">
            <div class="p-8">
                <h3 class="text-2xl font-black text-adaptive mb-6">Upgrade Your License</h3>
                <div class="space-y-4">
                    <div v-for="plan in upgradingLicense?.upgrade_plans" :key="plan.id" class="p-6 border border-white/5 rounded-[24px] flex items-center justify-between hover:border-brand-teal transition-all group">
                        <div>
                            <div class="font-black text-adaptive">{{ plan.name }}</div>
                            <div class="text-xs text-text-muted font-bold uppercase tracking-widest mt-1">{{ plan.type }} Edition</div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-xl font-black text-adaptive">${{ plan.amount }}</div>
                            <button @click="handleUpgrade(upgradingLicense, plan.id)" class="px-6 py-3 bg-brand-teal text-slate-900 text-xs font-black uppercase tracking-widest rounded-xl hover:scale-105 transition-all shadow-lg shadow-brand-teal/10">
                                Select
                            </button>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <SecondaryButton @click="upgradingLicense = null">Cancel</SecondaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
