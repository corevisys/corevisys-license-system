<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    orders: Array
});

const search = ref('');
const statusFilter = ref('pending'); // 'pending', 'completed', 'all'

const filteredOrders = computed(() => {
    return props.orders.filter(order => {
        const matchesStatus = statusFilter.value === 'all' || order.status === statusFilter.value;
        const matchesSearch = 
            order.order_number.toLowerCase().includes(search.value.toLowerCase()) ||
            order.user_email.toLowerCase().includes(search.value.toLowerCase()) ||
            (order.transaction_id && order.transaction_id.toLowerCase().includes(search.value.toLowerCase()));
        
        return matchesStatus && matchesSearch;
    });
});

const verifyOrder = (id) => {
    if (confirm('Are you sure you want to verify this order?')) {
        router.post(route('admin.orders.verify', id));
    }
};

const getStatusColor = (status) => {
    switch (status) {
        case 'pending': return 'bg-amber-500/10 text-amber-500 border-amber-500/20';
        case 'completed': return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
        case 'cancelled': return 'bg-rose-500/10 text-rose-500 border-rose-500/20';
        default: return 'bg-slate-500/10 text-slate-500 border-slate-500/20';
    }
};
</script>

<template>
    <Head title="Order Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-black text-xl text-adaptive uppercase tracking-widest leading-tight">
                    Order <span class="text-brand-teal">Management</span>
                </h2>
                <div class="flex gap-4">
                    <div class="relative group">
                        <input 
                            v-model="search"
                            type="text" 
                            placeholder="Search orders..." 
                            class="pl-10 pr-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm text-adaptive placeholder:text-text-muted focus:ring-1 focus:ring-brand-teal/50 transition-all w-64"
                        >
                        <svg class="w-4 h-4 text-text-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <!-- Stats overview could go here -->
            
            <!-- Filters -->
            <div class="flex gap-2 mb-6">
                <button 
                    v-for="status in ['pending', 'completed', 'all']" 
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
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Order ID</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">User</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Amount</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Method</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Trx ID</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted">Status</th>
                                <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-text-muted text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            <tr v-for="order in filteredOrders" :key="order.id" class="group hover:bg-white/[0.02] transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs text-brand-teal">{{ order.order_number }}</span>
                                    <p class="text-[10px] text-text-muted">{{ order.created_at }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-adaptive">{{ order.user_name }}</p>
                                    <p class="text-xs text-text-muted">{{ order.user_email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-bold text-adaptive">{{ order.currency }} {{ order.total_amount }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <template v-if="order.payment_method === 'online'">
                                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            <span class="text-xs font-bold text-indigo-400">Stripe</span>
                                        </template>
                                        <template v-else>
                                            <svg class="w-3.5 h-3.5 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                            <span class="text-xs font-medium text-text-muted capitalize">Manual</span>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs text-text-muted">{{ order.transaction_id }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded text-[10px] font-black uppercase tracking-widest border" :class="getStatusColor(order.status)">
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        v-if="order.status === 'pending'"
                                        @click="verifyOrder(order.id)"
                                        class="px-3 py-1 bg-brand-teal/10 text-brand-teal border border-brand-teal/20 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-brand-teal hover:text-slate-900 transition-all"
                                    >
                                        Verify
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="filteredOrders.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-text-muted text-sm italic">
                                    No orders found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
