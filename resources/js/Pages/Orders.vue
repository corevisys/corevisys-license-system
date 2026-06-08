<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    orders: {
        type: Array,
        required: true,
    }
});

const generateInvoice = (id) => {
    alert('Generating invoice for order #' + String(id).padStart(5, '0') + '... Your download will start shortly.');
};
</script>

<template>
    <Head title="Orders & Payments" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="mb-12">
            <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">History</h2>
            <p class="text-text-muted font-medium italic">Track your purchases and secure your invoices.</p>
        </div>

        <!-- Table Container -->
        <div class="bg-bg-dark/50 backdrop-blur-md rounded-[40px] shadow-soft-md border border-white/5 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-transparent">
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Reference</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Date Issued</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Value</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5">Method</th>
                        <th class="px-8 py-6 text-[10px] font-black text-text-muted uppercase tracking-[0.2em] border-b border-white/5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="order in orders" :key="order.id" class="group hover:bg-white/5 transition-all cursor-default">
                        <td class="px-8 py-6 font-mono text-sm font-bold text-indigo-600 dark:text-indigo-400">
                            #{{ String(order.id).padStart(5, '0') }}
                        </td>
                        <td class="px-8 py-6 text-sm font-medium text-text-muted">
                            {{ order.created_at }}
                        </td>
                        <td class="px-8 py-6 font-black text-adaptive">
                            {{ order.amount }} <span class="text-[10px] text-text-muted">{{ order.currency }}</span>
                        </td>
                        <td class="px-8 py-6">
                            <span :class="[
                                'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter',
                                order.status === 'completed' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400'
                            ]">
                                {{ order.status }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-[10px] font-black uppercase tracking-widest leading-none">
                            <div class="flex items-center gap-2">
                                <template v-if="order.payment_method === 'online'">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span class="text-indigo-600 dark:text-indigo-400">Stripe</span>
                                </template>
                                <template v-else>
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                    <span>Manual</span>
                                </template>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <button @click="generateInvoice(order.id)" class="px-4 py-2 bg-white/5 text-text-muted rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 hover:text-indigo-600 transition-all opacity-0 group-hover:opacity-100 border border-white/10 flex items-center gap-2 ml-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Invoice
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
