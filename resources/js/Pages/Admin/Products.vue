<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    }
});

const isModalOpen = ref(false);
const editingProduct = ref(null);

const form = useForm({
    id: null,
    name: '',
    description: '',
    is_active: true,
    prices: [
        { currency: 'USD', amount: 0, type: 'full', billing_period: null },
        { currency: 'BDT', amount: 0, type: 'full', billing_period: null }
    ]
});

const openCreateModal = () => {
    editingProduct.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (product) => {
    editingProduct.value = product;
    form.id = product.id;
    form.name = product.name;
    form.description = product.description;
    form.is_active = product.is_active;
    
    // Ensure we have at least USD and BDT placeholder if prices are empty
    form.prices = product.prices.length > 0 ? product.prices.map(p => ({
        currency: p.currency,
        amount: p.amount,
        type: p.type,
        billing_period: p.billing_period
    })) : [
        { currency: 'USD', amount: 0, type: 'full', billing_period: null },
        { currency: 'BDT', amount: 0, type: 'full', billing_period: null }
    ];
    
    isModalOpen.value = true;
};

const saveProduct = () => {
    form.post(route('admin.products.save'), {
        onSuccess: () => {
            isModalOpen.value = false;
        }
    });
};

const deleteProduct = (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        form.post(route('admin.products.delete'), {
            data: { id },
            preserveScroll: true
        });
    }
};

const addPriceRow = () => {
    form.prices.push({ currency: 'USD', amount: 0, type: 'full', billing_period: null });
};

const removePriceRow = (index) => {
    form.prices.splice(index, 1);
};
</script>

<template>
    <Head title="Product Management" />

    <AuthenticatedLayout>
        <!-- Header -->
        <div class="mb-12 flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">
                    Product <span class="text-brand-teal text-glow-teal">Inventory</span>
                </h2>
                <p class="text-text-muted font-medium">Manage your software catalog and multi-region pricing.</p>
            </div>
            <button @click="openCreateModal" 
                    class="px-8 py-3 bg-brand-teal/20 border border-brand-teal/30 text-brand-teal rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-brand-teal hover:text-slate-900 transition-all active:scale-95">
                New Product
            </button>
        </div>

        <!-- Product Table -->
        <div class="glass rounded-[40px] overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-white/5 bg-white/5">
                        <th class="px-8 py-5 text-[10px] font-black text-text-muted uppercase tracking-widest">Product</th>
                        <th class="px-8 py-5 text-[10px] font-black text-text-muted uppercase tracking-widest">Status</th>
                        <th class="px-8 py-5 text-[10px] font-black text-text-muted uppercase tracking-widest">Pricing</th>
                        <th class="px-8 py-5 text-[10px] font-black text-text-muted uppercase tracking-widest text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <tr v-for="product in products" :key="product.id" class="group hover:bg-white/[0.02] transition-colors">
                        <td class="px-8 py-6">
                            <div class="font-black text-adaptive text-lg">{{ product.name }}</div>
                            <div class="text-xs text-text-muted font-medium truncate max-w-xs">{{ product.description }}</div>
                        </td>
                        <td class="px-8 py-6">
                            <span :class="product.is_active ? 'text-brand-teal' : 'text-text-muted'" 
                                  class="text-[10px] font-black uppercase tracking-widest flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" :class="product.is_active ? 'bg-brand-teal shadow-[0_0_8px_rgba(45,212,191,0.5)]' : 'bg-white/20'"></span>
                                {{ product.is_active ? 'Active' : 'Draft' }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex gap-2">
                                <div v-for="price in product.prices" :key="price.id" 
                                     class="px-3 py-1 bg-white/5 border border-white/10 rounded-full text-[10px] font-black text-adaptive uppercase">
                                    {{ price.currency }} {{ price.amount }}
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEditModal(product)" class="p-2 text-text-muted hover:text-adaptive transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                <button @click="deleteProduct(product.id)" class="p-2 text-text-muted hover:text-rose-500 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-6 sm:p-12">
            <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-md" @click="isModalOpen = false"></div>
            
            <div class="relative w-full max-w-4xl glass rounded-[40px] shadow-2xl overflow-hidden border border-white/10 animate-fade-in-up">
                <div class="p-10">
                    <h3 class="text-2xl font-black text-adaptive mb-8">{{ editingProduct ? 'Configure Product' : 'Create New Product' }}</h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <!-- Basic Info -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black text-text-muted uppercase tracking-widest mb-2">Product Name</label>
                                <input v-model="form.name" type="text" class="w-full glass border-none rounded-2xl p-4 text-sm font-bold text-adaptive focus:ring-1 focus:ring-brand-teal/50 transition-all">
                                <div v-if="form.errors.name" class="text-rose-500 text-[10px] mt-1 font-black uppercase">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-text-muted uppercase tracking-widest mb-2">Technical Description</label>
                                <textarea v-model="form.description" rows="4" class="w-full glass border-none rounded-2xl p-4 text-sm font-bold text-adaptive focus:ring-1 focus:ring-brand-teal/50 transition-all resize-none"></textarea>
                            </div>
                            <div class="flex items-center gap-4 p-4 glass rounded-2xl">
                                <button @click="form.is_active = !form.is_active" 
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                        :class="form.is_active ? 'bg-brand-teal' : 'bg-white/10'">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                          :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"></span>
                                </button>
                                <span class="text-[10px] font-black text-adaptive uppercase tracking-widest">Public Deployment State</span>
                            </div>
                        </div>

                        <!-- Pricing -->
                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <label class="block text-[10px] font-black text-text-muted uppercase tracking-widest">Fiscal Units</label>
                                <button @click="addPriceRow" class="text-[10px] font-black text-brand-teal uppercase tracking-widest hover:underline">+ Add Currency</button>
                            </div>
                            
                            <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                                <div v-for="(price, index) in form.prices" :key="index" class="p-4 glass rounded-2xl border border-white/5 space-y-3 relative group">
                                    <button @click="removePriceRow(index)" class="absolute top-2 right-2 text-slate-500 hover:text-rose-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                    
                                    <div class="flex gap-3">
                                        <div class="w-1/3">
                                            <input v-model="price.currency" type="text" placeholder="USD" class="w-full bg-white/5 border-none rounded-xl p-2 text-xs font-black text-adaptive uppercase text-center focus:ring-1 focus:ring-brand-teal/50 transition-all">
                                        </div>
                                        <div class="flex-1">
                                            <input v-model="price.amount" type="number" placeholder="Price" class="w-full bg-white/5 border-none rounded-xl p-2 text-xs font-black text-adaptive focus:ring-1 focus:ring-brand-teal/50 transition-all">
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <select v-model="price.type" class="flex-1 bg-white/5 border-none rounded-xl p-2 text-[10px] font-black text-adaptive uppercase focus:ring-1 focus:ring-brand-teal/50 transition-all appearance-none">
                                            <option value="full">Full License</option>
                                            <option value="subscription">Subscription</option>
                                            <option value="trial">Trial</option>
                                        </select>
                                        <div v-if="price.type === 'subscription'" class="w-1/3">
                                            <input v-model="price.billing_period" type="number" placeholder="Days" class="w-full bg-white/5 border-none rounded-xl p-2 text-xs font-black text-adaptive focus:ring-1 focus:ring-brand-teal/50 transition-all">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-6 mt-12 pt-8 border-t border-white/5">
                        <button @click="isModalOpen = false" class="text-[10px] font-black text-text-muted uppercase tracking-widest hover:text-adaptive transition-colors">Cancel</button>
                        <button @click="saveProduct" 
                                :disabled="form.processing"
                                class="px-12 py-4 bg-brand-teal text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-brand-teal/20 transition-all active:scale-95 disabled:opacity-50">
                            {{ form.processing ? 'Syncing...' : 'Deploy Inventory' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
</style>
