<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
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
        { currency: 'BDT', amount: 0, type: 'full', billing_period: null },
    ],
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

    form.prices = product.prices.length > 0 ? product.prices.map((p) => ({
        currency: p.currency,
        amount: p.amount,
        type: p.type,
        billing_period: p.billing_period,
    })) : [
        { currency: 'USD', amount: 0, type: 'full', billing_period: null },
        { currency: 'BDT', amount: 0, type: 'full', billing_period: null },
    ];

    isModalOpen.value = true;
};

const saveProduct = () => {
    form.post(route('admin.products.save'), {
        onSuccess: () => {
            isModalOpen.value = false;
        },
    });
};

const deleteProduct = (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        form.post(route('admin.products.delete'), {
            data: { id },
            preserveScroll: true,
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
        <div class="mb-12 flex items-center justify-between gap-4">
            <div>
                <Badge status="success" class="!rounded-full px-3 py-1.5">Catalog</Badge>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">Product inventory</h2>
                <p class="mt-2 text-sm text-text-muted">Manage your software catalog and multi-region pricing.</p>
            </div>
            <Button type="button" variant="primary" @click="openCreateModal">New product</Button>
        </div>

        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="border-b border-panel-line bg-panel-2">
                        <tr>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Product</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Status</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Pricing</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in products" :key="product.id" class="border-b border-panel-line last:border-b-0 hover:bg-panel-2/50">
                            <td class="px-8 py-6">
                                <div class="text-lg font-black text-text-primary">{{ product.name }}</div>
                                <div class="max-w-xs truncate text-xs font-medium text-text-muted">{{ product.description }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em]" :class="product.is_active ? 'text-teal' : 'text-text-muted'">
                                    <span class="h-2 w-2 rounded-full" :class="product.is_active ? 'bg-teal' : 'bg-panel-line'" />
                                    {{ product.is_active ? 'Active' : 'Draft' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-wrap gap-2">
                                    <span v-for="price in product.prices" :key="price.id" class="rounded-full border border-panel-line bg-panel-2 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-text-primary">
                                        {{ price.currency }} {{ price.amount }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-3">
                                    <button @click="openEditModal(product)" class="rounded-xl border border-panel-line bg-panel-2 p-2 text-text-muted transition hover:border-amber hover:text-amber">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                    </button>
                                    <button @click="deleteProduct(product.id)" class="rounded-xl border border-panel-line bg-panel-2 p-2 text-text-muted transition hover:border-danger hover:text-danger">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-6 sm:p-12">
            <div class="absolute inset-0 bg-[#050b12]/80 backdrop-blur-md" @click="isModalOpen = false"></div>

            <div class="relative w-full max-w-4xl overflow-hidden rounded-[24px] border border-panel-line bg-panel-2 shadow-2xl">
                <div class="p-10">
                    <h3 class="mb-8 text-2xl font-black text-text-primary">{{ editingProduct ? 'Configure product' : 'Create new product' }}</h3>

                    <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                        <div class="space-y-6">
                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Product name</label>
                                <input v-model="form.name" type="text" class="w-full rounded-2xl border border-panel-line bg-panel-3 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                                <div v-if="form.errors.name" class="mt-1 text-[10px] font-black uppercase text-danger">{{ form.errors.name }}</div>
                            </div>
                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Technical description</label>
                                <textarea v-model="form.description" rows="4" class="w-full resize-none rounded-2xl border border-panel-line bg-panel-3 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none"></textarea>
                            </div>
                            <div class="flex items-center gap-4 rounded-2xl border border-panel-line bg-panel-3 p-4">
                                <button @click="form.is_active = !form.is_active" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="form.is_active ? 'bg-teal' : 'bg-panel-line'">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.is_active ? 'translate-x-6' : 'translate-x-1'" />
                                </button>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-text-primary">Public deployment state</span>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center justify-between">
                                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Fiscal units</label>
                                <button @click="addPriceRow" class="text-[10px] font-black uppercase tracking-[0.2em] text-teal hover:underline">+ Add currency</button>
                            </div>

                            <div class="max-h-[300px] space-y-4 overflow-y-auto pr-2">
                                <div v-for="(price, index) in form.prices" :key="index" class="relative space-y-3 rounded-2xl border border-panel-line bg-panel-3 p-4">
                                    <button @click="removePriceRow(index)" class="absolute right-2 top-2 text-text-muted transition hover:text-danger">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>

                                    <div class="flex gap-3">
                                        <div class="w-1/3">
                                            <input v-model="price.currency" type="text" placeholder="USD" class="w-full rounded-xl border border-panel-line bg-panel-2 p-2 text-center text-xs font-black uppercase text-text-primary focus:border-amber focus:outline-none">
                                        </div>
                                        <div class="flex-1">
                                            <input v-model="price.amount" type="number" placeholder="Price" class="w-full rounded-xl border border-panel-line bg-panel-2 p-2 text-xs font-black text-text-primary focus:border-amber focus:outline-none">
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <select v-model="price.type" class="flex-1 appearance-none rounded-xl border border-panel-line bg-panel-2 p-2 text-[10px] font-black uppercase text-text-primary focus:border-amber focus:outline-none">
                                            <option value="full">Full License</option>
                                            <option value="subscription">Subscription</option>
                                            <option value="trial">Trial</option>
                                        </select>
                                        <div v-if="price.type === 'subscription'" class="w-1/3">
                                            <input v-model="price.billing_period" type="number" placeholder="Days" class="w-full rounded-xl border border-panel-line bg-panel-2 p-2 text-xs font-black text-text-primary focus:border-amber focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 flex justify-end gap-6 border-t border-panel-line pt-8">
                        <button @click="isModalOpen = false" class="text-[10px] font-black uppercase tracking-[0.2em] text-text-muted transition hover:text-text-primary">Cancel</button>
                        <Button type="button" variant="primary" :disabled="form.processing" @click="saveProduct">
                            {{ form.processing ? 'Syncing...' : 'Deploy inventory' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
