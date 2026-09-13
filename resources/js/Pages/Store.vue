<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    gatewaySettings: {
        type: Object,
        default: () => ({}),
    },
});

const formatAmount = (amount) => Number(amount).toLocaleString(undefined, {
    minimumFractionDigits: Number(amount) % 1 === 0 ? 0 : 2,
    maximumFractionDigits: 2,
});

const checkout = ref(null);
const selectedGateway = ref(null);

const openCheckout = (product, price) => {
    checkout.value = { product, price };
    selectedGateway.value = null;
};

const isFree = computed(() =>
    checkout.value ? Number(checkout.value.price.amount) === 0 : false,
);

const gateways = computed(() => {
    const list = [];

    if (!isFree.value && props.gatewaySettings.gateway_stripe_active === '1') {
        list.push({
            id: 'stripe',
            method: 'online',
            gateway: 'stripe',
            label: 'Stripe',
            desc: 'Pay with credit / debit card',
            classes: 'border-indigo-500/30 bg-indigo-500/10 text-indigo-400',
            dot: 'bg-indigo-500',
        });
    }

    if (!isFree.value && props.gatewaySettings.gateway_bkash_active === '1') {
        list.push({
            id: 'bkash',
            method: 'online',
            gateway: 'bkash',
            label: 'bKash',
            desc: 'Pay via bKash mobile wallet',
            classes: 'border-pink-500/30 bg-pink-500/10 text-pink-400',
            dot: 'bg-pink-500',
        });
    }

    list.push({
        id: 'offline',
        method: 'offline',
        gateway: 'manual',
        label: isFree.value ? 'Free License' : 'Offline',
        desc: isFree.value ? 'No payment required' : 'Bank transfer / manual — verified by admin',
        classes: 'border-white/10 bg-white/5 text-text-muted',
        dot: 'bg-slate-500',
    });

    return list;
});

const submitting = ref(false);

const proceedToPayment = () => {
    if (!checkout.value || !selectedGateway.value) return;

    submitting.value = true;
    const gateway = gateways.value.find((g) => g.id === selectedGateway.value);

    router.post(route('order.create'), {
        product_id: checkout.value.product.id,
        price_id: checkout.value.price.id,
        payment_method: gateway.method,
        gateway: gateway.gateway,
    }, {
        onFinish: () => {
            submitting.value = false;
        },
    });
};
</script>

<template>
    <Head title="Store" />

    <AuthenticatedLayout>
        <div class="mb-12">
            <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">License Store</h2>
            <p class="text-text-muted font-medium italic">Pick a product, choose a plan, and activate your license in seconds.</p>
        </div>

        <div v-if="products.length === 0" class="bg-bg-dark/50 backdrop-blur-md rounded-[40px] shadow-soft-md border border-white/5 p-20 text-center">
            <p class="text-sm font-bold text-text-muted uppercase tracking-[0.2em]">No products available yet</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            <div
                v-for="product in products"
                :key="product.id"
                class="bg-bg-dark/50 backdrop-blur-md rounded-[40px] shadow-soft-md border border-white/5 p-8 flex flex-col"
            >
                <div class="flex items-center justify-between mb-6">
                    <div class="w-12 h-12 bg-brand-teal/10 border border-brand-teal/20 rounded-2xl flex items-center justify-center text-brand-teal">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-[10px] font-black uppercase tracking-widest border border-emerald-500/20">Available</span>
                </div>

                <h3 class="text-2xl font-black text-adaptive mb-2">{{ product.name }}</h3>
                <p v-if="product.description" class="text-sm text-text-muted leading-relaxed mb-6">{{ product.description }}</p>

                <div class="space-y-4 mt-auto">
                    <div
                        v-for="price in product.prices"
                        :key="price.id"
                        class="p-6 border border-white/5 rounded-[24px] hover:border-brand-teal/40 transition-all"
                    >
                        <div class="flex items-baseline justify-between mb-3">
                            <span class="text-[10px] font-black uppercase tracking-widest text-text-muted">{{ price.type }}</span>
                            <div>
                                <span class="text-xl font-black text-adaptive">{{ formatAmount(price.amount) }} {{ price.currency }}</span>
                            </div>
                        </div>
                        <div v-if="price.billing_period" class="text-[10px] font-bold text-text-muted uppercase tracking-widest mb-4">
                            {{ price.billing_period >= 365 ? 'Yearly' : 'Monthly' }} billing
                        </div>
                        <button
                            @click="openCheckout(product, price)"
                            class="w-full py-3 bg-brand-teal text-slate-900 text-xs font-black uppercase tracking-widest rounded-xl hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-brand-teal/20"
                        >
                            Purchase
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="!!checkout" @close="checkout = null">
            <div class="p-8 relative">
                <h3 class="text-2xl font-black text-adaptive mb-1">Checkout</h3>
                <p v-if="checkout" class="text-xs text-text-muted font-bold uppercase tracking-widest mb-6">
                    {{ checkout.product.name }} — {{ checkout.price.type }} plan
                </p>

                <div v-if="checkout" class="bg-white/5 border border-white/10 rounded-[24px] p-6 mb-6 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-1">Total Due</div>
                        <div class="text-3xl font-black text-adaptive">
                            {{ formatAmount(checkout.price.amount) }} {{ checkout.price.currency }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-1">Billing</div>
                        <div class="text-sm font-bold text-adaptive text-right">
                            {{ checkout.price.billing_period ? (checkout.price.billing_period >= 365 ? 'Yearly' : 'Monthly') : 'One-time' }}
                        </div>
                    </div>
                </div>

                <p class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-3">Payment Method</p>
                <div class="space-y-3 mb-8">
                    <button
                        v-for="gateway in gateways"
                        :key="gateway.id"
                        @click="selectedGateway = gateway.id"
                        class="w-full p-5 rounded-[20px] border text-left transition-all flex items-center justify-between"
                        :class="selectedGateway === gateway.id
                            ? gateway.classes + ' ring-2 ring-offset-0 ring-brand-teal/40'
                            : 'border-white/5 bg-white/5 text-text-muted hover:border-white/15'"
                    >
                        <div class="flex items-center gap-4">
                            <span class="w-3 h-3 rounded-full" :class="selectedGateway === gateway.id ? gateway.dot : 'bg-slate-700'"></span>
                            <div>
                                <div class="font-black text-sm">{{ gateway.label }}</div>
                                <div class="text-[10px] font-bold text-text-muted uppercase tracking-widest mt-0.5">{{ gateway.desc }}</div>
                            </div>
                        </div>
                        <svg v-if="selectedGateway === gateway.id" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </button>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <SecondaryButton @click="checkout = null">Cancel</SecondaryButton>
                    <button
                        :disabled="!selectedGateway || submitting"
                        @click="proceedToPayment"
                        class="px-8 py-3 bg-brand-teal text-slate-900 text-xs font-black uppercase tracking-widest rounded-xl hover:scale-105 active:scale-95 transition-all shadow-lg shadow-brand-teal/20 disabled:opacity-40 disabled:pointer-events-none"
                    >
                        {{ submitting ? 'Processing...' : (isFree ? 'Get License' : 'Proceed to Payment') }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>