<script setup>
import Alert from '@/Components/UI/Alert.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Modal from '@/Components/Modal.vue';

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
            classes: 'border-provider-stripe/30 bg-provider-stripe/10 text-provider-stripe',
            dot: 'bg-provider-stripe',
        });
    }

    if (!isFree.value && props.gatewaySettings.gateway_bkash_active === '1') {
        list.push({
            id: 'bkash',
            method: 'online',
            gateway: 'bkash',
            label: 'bKash',
            desc: 'Pay via bKash mobile wallet',
            classes: 'border-provider-bkash/30 bg-provider-bkash/10 text-provider-bkash',
            dot: 'bg-provider-bkash',
        });
    }

    list.push({
        id: 'offline',
        method: 'offline',
        gateway: 'manual',
        label: isFree.value ? 'Free License' : 'Offline',
        desc: isFree.value ? 'No payment required' : 'Bank transfer / manual — verified by admin',
        classes: 'border-panel-line bg-panel-2 text-text-muted',
        dot: 'bg-provider-offline',
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
            <Badge status="success" class="!rounded-full px-3 py-1.5">License Store</Badge>
            <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">License Store</h2>
            <p class="mt-2 text-sm text-text-muted">Pick a product, choose a plan, and activate your license in seconds.</p>
        </div>

        <div v-if="products.length === 0" class="rounded-[32px] border border-panel-line bg-panel-2 p-20 text-center">
            <p class="text-sm font-black uppercase tracking-[0.2em] text-text-muted">No products available yet</p>
        </div>

        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 xl:grid-cols-3">
            <Card
                v-for="product in products"
                :key="product.id"
                class="flex flex-col p-8"
            >
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                    </div>
                    <Badge status="success" class="!rounded-full !px-3 !py-1 !text-[10px]">Available</Badge>
                </div>

                <h3 class="mb-2 text-2xl font-black text-text-primary">{{ product.name }}</h3>
                <p v-if="product.description" class="mb-6 text-sm leading-relaxed text-text-muted">{{ product.description }}</p>

                <div class="mt-auto space-y-4">
                    <div
                        v-for="price in product.prices"
                        :key="price.id"
                        class="rounded-[24px] border border-panel-line bg-panel-2 p-5 transition-all hover:border-amber/50"
                    >
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">{{ price.type }}</span>
                            <Badge status="default" class="!rounded-full !px-2 !py-1 !text-[9px]">{{ price.billing_period >= 365 ? 'Annual' : 'Plan' }}</Badge>
                        </div>
                        <div class="mb-4 flex items-end justify-between gap-3">
                            <div class="text-xl font-black text-text-primary">{{ formatAmount(price.amount) }} {{ price.currency }}</div>
                            <div v-if="price.billing_period" class="text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">
                                {{ price.billing_period >= 365 ? 'Yearly' : 'Monthly' }}
                            </div>
                        </div>
                        <Button type="button" variant="primary" class="w-full justify-center" @click="openCheckout(product, price)">
                            Purchase
                        </Button>
                    </div>
                </div>
            </Card>
        </div>

        <Modal :show="!!checkout" @close="checkout = null">
            <div class="relative p-8">
                <h3 class="mb-1 text-2xl font-black text-text-primary">Checkout</h3>
                <p v-if="checkout" class="mb-6 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">
                    {{ checkout.product.name }} — {{ checkout.price.type }} plan
                </p>

                <Alert v-if="submitting" variant="success" class="mb-6">Processing your order…</Alert>

                <div v-if="checkout" class="mb-6 flex items-center justify-between rounded-[24px] border border-panel-line bg-panel-2 p-6">
                    <div>
                        <div class="mb-1 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Total Due</div>
                        <div class="font-mono text-3xl font-black text-text-primary">
                            {{ formatAmount(checkout.price.amount) }} {{ checkout.price.currency }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="mb-1 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Billing</div>
                        <div class="text-sm font-bold text-text-primary">
                            {{ checkout.price.billing_period ? (checkout.price.billing_period >= 365 ? 'Yearly' : 'Monthly') : 'One-time' }}
                        </div>
                    </div>
                </div>

                <p class="mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Payment Method</p>
                <div class="mb-8 space-y-3">
                    <button
                        v-for="gateway in gateways"
                        :key="gateway.id"
                        @click="selectedGateway = gateway.id"
                        class="flex w-full items-center justify-between rounded-[20px] border p-5 text-left transition-all"
                        :class="selectedGateway === gateway.id
                            ? 'border-amber bg-panel-2 ring-1 ring-amber/30'
                            : 'border-panel-line bg-panel-2 text-text-muted hover:border-amber/50 hover:bg-panel'"
                    >
                        <div class="flex items-center gap-4">
                            <span class="h-3 w-3 rounded-full" :class="selectedGateway === gateway.id ? gateway.dot : 'bg-text-muted'" />
                            <div>
                                <div class="text-sm font-black text-text-primary">{{ gateway.label }}</div>
                                <div class="mt-0.5 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">{{ gateway.desc }}</div>
                            </div>
                        </div>
                        <svg v-if="selectedGateway === gateway.id" class="h-5 w-5 text-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                    </button>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Button type="button" variant="secondary" @click="checkout = null">Cancel</Button>
                    <Button
                        type="button"
                        variant="primary"
                        :disabled="!selectedGateway || submitting"
                        @click="proceedToPayment"
                    >
                        {{ submitting ? 'Processing...' : (isFree ? 'Get License' : 'Proceed to Payment') }}
                    </Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>