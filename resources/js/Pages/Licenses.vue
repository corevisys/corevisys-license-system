<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Alert from '@/Components/UI/Alert.vue';
import Modal from '@/Components/Modal.vue';
import Table from '@/Components/UI/Table.vue';
import TableCell from '@/Components/UI/TableCell.vue';
import TableHead from '@/Components/UI/TableHead.vue';
import TableHeaderCell from '@/Components/UI/TableHeaderCell.vue';
import TableRow from '@/Components/UI/TableRow.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref, watchEffect } from 'vue';
import axios from 'axios';
import { Copy, LoaderCircle } from 'lucide-vue-next';

const props = defineProps({
    licenses: {
        type: Array,
        required: false,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    availableGateways: {
        type: Array,
        default: () => ['offline'],
    },
});

const keyMessage = ref('');
const keyMessageIsError = ref(false);
const revealingLicenseId = ref(null);

const copyKey = async (license) => {
    keyMessage.value = '';
    keyMessageIsError.value = false;
    revealingLicenseId.value = license.id;

    try {
        const response = await axios.post(route('licenses.reveal', license.id));
        await navigator.clipboard.writeText(response.data.license_key);
        keyMessage.value = 'License key copied to clipboard.';
    } catch (error) {
        keyMessageIsError.value = true;
        keyMessage.value = error.response?.data?.message ?? 'Unable to retrieve the license key. Try again later.';
    } finally {
        revealingLicenseId.value = null;
    }
};

const managingLicense = ref(null);
const upgradingLicense = ref(null);

const statusVariant = (status) => {
    const value = String(status || '').toLowerCase();
    const mapping = {
        inactive: 'default',
        active: 'success',
        expired: 'danger',
        suspended: 'danger',
        revoked: 'danger',
    };

    return mapping[value] ?? 'default';
};

const upgradeGateway = ref('');

const upgradeError = ref('');

// Map ISO currency codes to a display symbol. Unknown codes fall back to the
// code itself (e.g. 'BDT') rather than silently assuming '$'.
const currencyLabel = (currency) => {
    const code = String(currency || 'USD').toUpperCase();
    return { USD: '$', BDT: '৳', EUR: '€', GBP: '£', INR: '₹' }[code] ?? code;
};

const formatMoney = (amount, currency) => `${currencyLabel(currency)} ${amount}`;

const openUpgrade = (license) => {
    upgradingLicense.value = license;
    upgradeGateway.value = gatewayOptions.value[0]?.id ?? 'offline';
    upgradeError.value = '';
};

const closeUpgrade = () => {
    upgradingLicense.value = null;
    upgradeGateway.value = '';
    upgradeError.value = '';
};

const handleUpgrade = (license, priceId) => {
    if (!upgradeGateway.value) {
        upgradeError.value = 'Please choose a payment method to continue.';
        return;
    }

    upgradeError.value = '';
    router.post(route('licenses.upgrade', license.id), {
        product_price_id: priceId,
        gateway: upgradeGateway.value,
    });
};

const renewingLicense = ref(null);
const selectedGateway = ref('');
const selectedPlanId = ref('');
const renewError = ref('');

const page = usePage();

// Sync backend flash messages into the renewal modal so the user sees
// errors/success inside the dialog instead of behind it.
watchEffect(() => {
    if (!renewingLicense.value) return;
    const flash = page.props.flash ?? {};
    if (flash.error) {
        renewError.value = flash.error;
        submittingRenewal.value = false;
    }
    if (flash.success) {
        closeRenew();
    }
});

const GATEWAY_META = {
    stripe: { label: 'Card (Stripe)', desc: 'Pay with a credit or debit card' },
    bkash: { label: 'bKash', desc: 'Pay via bKash mobile wallet' },
    offline: { label: 'Offline / Manual', desc: 'Bank transfer — verified by an admin' },
};

const gatewayOptions = computed(() => {
    const list = props.availableGateways.filter((g) => GATEWAY_META[g]);
    // Guarantee at least one usable option even if settings are missing.
    return (list.length ? list : ['offline']).map((id) => ({
        id,
        ...GATEWAY_META[id],
    }));
});

const openRenew = (license) => {
    renewingLicense.value = license;
    selectedPlanId.value = license.renew_plans?.[0]?.id ?? '';
    renewError.value = '';

    const gateways = gatewayOptions.value.map((g) => g.id);
    // Prefer the gateway previously used for this license, otherwise the first available.
    selectedGateway.value = gateways.includes(license.last_gateway)
        ? license.last_gateway
        : gateways[0];
};

const closeRenew = () => {
    renewingLicense.value = null;
    selectedGateway.value = '';
    selectedPlanId.value = '';
    renewError.value = '';
};

const selectedPlan = computed(() => {
    const plans = renewingLicense.value?.renew_plans ?? [];
    return plans.find((p) => p.id === selectedPlanId.value) ?? plans[0] ?? null;
});

const submittingRenewal = ref(false);

const submitRenewal = () => {
    if (!renewingLicense.value || !selectedGateway.value) {
        renewError.value = 'Please choose a payment method.';
        return;
    }

    if (!selectedPlanId.value) {
        renewError.value = 'No renewal plan is available for this license. Please contact support.';
        return;
    }

    submittingRenewal.value = true;
    renewError.value = '';

    router.post(route('licenses.renew', renewingLicense.value.id), {
        gateway: selectedGateway.value,
        product_price_id: selectedPlanId.value,
    }, {
        preserveScroll: true,
        // For online gateways (Stripe/bKash), Inertia.location() redirects
        // externally — onSuccess never fires and that's expected.
        // For offline, onSuccess fires when back()->with('success') is returned
        // and we close the modal so the page flash is visible.
        onSuccess: () => {
            closeRenew();
        },
        onError: () => {
            // Validation errors (422) land here; flash errors are handled by
            // the watchEffect above.
            submittingRenewal.value = false;
            renewError.value = renewError.value || 'Something went wrong. Please try again.';
        },
        onFinish: () => {
            submittingRenewal.value = false;
        },
    });
};

const openConfig = (license) => {
    router.get(route('licenses.config', license.id));
};

let pollInterval = null;

onMounted(() => {
    // Non-critical dashboard: poll infrequently to avoid sustained DB load
    // (was every 60s, multiplied across all concurrent user sessions).
    pollInterval = setInterval(() => {
        router.reload({
            only: ['licenses'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 180000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head title="My Licenses" />

    <AuthenticatedLayout>
        <div class="mb-12 flex items-center justify-between gap-4">
            <div>
                <Badge status="success" class="!rounded-full px-3 py-1.5">Asset vault</Badge>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">My assets</h2>
                <p class="mt-2 text-sm text-text-muted">Manage your active software seats and domain bindings.</p>
            </div>
            <Button type="button" variant="primary" @click="router.get(route('store'))">New license</Button>
        </div>

        <Alert v-if="keyMessage" :variant="keyMessageIsError ? 'danger' : 'success'" class="mb-6">
            {{ keyMessage }}
        </Alert>

        <Alert v-if="$page.props.flash?.error" variant="danger" class="mb-6">
            {{ $page.props.flash.error }}
        </Alert>
        <Alert v-else-if="$page.props.flash?.success" variant="success" class="mb-6">
            {{ $page.props.flash.success }}
        </Alert>
        <Alert v-else-if="$page.props.flash?.info" variant="default" class="mb-6">
            {{ $page.props.flash.info }}
        </Alert>

        <Card class="overflow-hidden p-0">
            <div v-if="loading" class="space-y-4 p-6">
                <div v-for="i in 5" :key="i" class="grid grid-cols-6 gap-4 animate-pulse">
                    <div class="h-8 rounded-lg bg-panel-line" />
                    <div class="h-8 rounded-lg bg-panel-line" />
                    <div class="h-8 rounded-lg bg-panel-line" />
                    <div class="h-8 rounded-lg bg-panel-line" />
                    <div class="h-8 rounded-lg bg-panel-line" />
                    <div class="h-8 rounded-lg bg-panel-line" />
                </div>
            </div>

            <div v-else-if="licenses.length === 0" class="p-20">
                <TableEmpty>Secure vault is empty. New licenses will show up here.</TableEmpty>
            </div>

            <div v-else class="overflow-x-auto">
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeaderCell>Product</TableHeaderCell>
                            <TableHeaderCell>Security key</TableHeaderCell>
                            <TableHeaderCell>Health</TableHeaderCell>
                            <TableHeaderCell>Environment</TableHeaderCell>
                            <TableHeaderCell>Valid until</TableHeaderCell>
                            <TableHeaderCell class="text-right">Actions</TableHeaderCell>
                        </TableRow>
                    </TableHead>
                    <tbody>
                        <TableRow v-for="license in licenses" :key="license.id">
                            <TableCell>
                                <div class="font-black text-text-primary">{{ license.product_name }}</div>
                                <div class="text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">{{ license.type }} edition</div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <code class="rounded-xl border border-panel-line bg-panel-2 px-3 py-1.5 font-mono text-sm text-teal">
                                        {{ license.key_preview }}
                                    </code>
                                    <button type="button" :disabled="revealingLicenseId === license.id" @click="copyKey(license)" class="rounded-xl border border-panel-line p-2 text-text-muted transition hover:border-amber hover:text-amber disabled:cursor-wait disabled:opacity-60 [&>svg:last-child]:hidden" title="Reveal and copy license key">
                                        <LoaderCircle v-if="revealingLicenseId === license.id" class="h-4 w-4 animate-spin" />
                                        <Copy v-else class="h-4 w-4" />
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                    </button>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="space-y-2">
                                    <Badge :status="statusVariant(license.status)">{{ license.status }}</Badge>
                                    <div class="flex items-center gap-2 text-[9px] font-black uppercase tracking-[0.2em]">
                                        <span :class="license.is_running ? 'h-1.5 w-1.5 rounded-full bg-teal' : 'h-1.5 w-1.5 rounded-full bg-panel-line'" />
                                        <span :class="license.is_running ? 'text-teal' : 'text-text-muted'">
                                            {{ license.is_running ? 'Live now' : 'Idle' }}
                                        </span>
                                    </div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="font-medium text-text-primary">{{ license.bound_domain || 'Not bound' }}</div>
                                <div class="mt-1 font-mono text-[10px] uppercase tracking-[0.2em] text-text-muted">{{ license.bound_ip || '---' }}</div>
                            </TableCell>
                            <TableCell>
                                <div :class="license.is_expiring_soon ? 'text-amber' : 'text-text-primary'">
                                    {{ license.expires_at || 'Lifetime' }}
                                </div>
                                <div v-if="license.is_expiring_soon" class="mt-1 text-[9px] font-black uppercase tracking-[0.2em] text-amber">Expiring soon</div>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button type="button" variant="secondary" @click="openConfig(license)">Config</Button>
                                    <Button v-if="license.upgrade_plans && license.upgrade_plans.length > 0" type="button" variant="primary" @click="openUpgrade(license)">Upgrade</Button>
                                    <Button v-if="license.can_renew" type="button" variant="secondary" @click="openRenew(license)">Renew</Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </tbody>
                </Table>
            </div>
        </Card>

        <Modal :show="!!upgradingLicense" @close="closeUpgrade">
            <div class="p-8">
                <h3 class="text-2xl font-black text-text-primary">Upgrade your license</h3>
                <p class="mt-2 text-sm text-text-muted">
                    Choose a plan and a payment method for
                    <span class="font-medium text-text-primary">{{ upgradingLicense?.product_name }}</span>.
                </p>

                <div v-if="upgradeError" class="mt-5">
                    <Alert variant="danger">{{ upgradeError }}</Alert>
                </div>

                <div class="mt-6">
                    <p class="mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Upgrade plan</p>
                    <div class="space-y-4">
                        <div v-for="plan in upgradingLicense?.upgrade_plans" :key="plan.id" class="flex items-center justify-between rounded-[24px] border border-panel-line bg-panel-2 p-6">
                            <div>
                                <div class="font-black text-text-primary">{{ plan.name }}</div>
                                <div class="mt-1 text-xs font-black uppercase tracking-[0.2em] text-text-muted">{{ plan.type }} edition</div>
                            </div>
                            <div class="flex items-center gap-6">
                                <div class="text-xl font-black text-text-primary">{{ formatMoney(plan.amount, plan.currency) }}</div>
                                <Button type="button" variant="primary" :disabled="!upgradeGateway" @click="handleUpgrade(upgradingLicense, plan.id)">Select</Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment gateway selection -->
                <div class="mt-6">
                    <p class="mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Payment method</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button
                            v-for="option in gatewayOptions"
                            :key="option.id"
                            type="button"
                            class="rounded-[24px] border p-5 text-left transition-colors"
                            :class="upgradeGateway === option.id ? 'border-amber bg-amber/10' : 'border-panel-line bg-panel-2 hover:border-amber/40'"
                            @click="upgradeGateway = option.id"
                        >
                            <div class="font-black text-text-primary">{{ option.label }}</div>
                            <div class="mt-1 text-xs text-text-muted">{{ option.desc }}</div>
                        </button>
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <Button type="button" variant="secondary" @click="closeUpgrade">Cancel</Button>
                </div>
            </div>
        </Modal>

        <Modal :show="!!renewingLicense" @close="closeRenew">
            <div class="p-8">
                <h3 class="text-2xl font-black text-text-primary">Renew license</h3>
                <p class="mt-2 text-sm text-text-muted">
                    Choose a plan and a payment method for
                    <span class="font-medium text-text-primary">{{ renewingLicense?.product_name }}</span>.
                </p>

                <div v-if="renewError" class="mt-5">
                    <Alert variant="danger">{{ renewError }}</Alert>
                </div>

                <!-- Renewal plan (monthly / yearly / custom duration) -->
                <div class="mt-6">
                    <p class="mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Renewal plan</p>

                    <div v-if="(renewingLicense?.renew_plans?.length ?? 0) === 0" class="rounded-[24px] border border-panel-line bg-panel-2 p-6 text-sm text-text-muted">
                        No renewal plan is configured for this product. Please contact support.
                    </div>

                    <div v-else class="space-y-3">
                        <button
                            v-for="plan in renewingLicense?.renew_plans"
                            :key="plan.id"
                            type="button"
                            class="flex w-full items-center justify-between rounded-[24px] border p-5 text-left transition-colors"
                            :class="selectedPlanId === plan.id ? 'border-amber bg-amber/10' : 'border-panel-line bg-panel-2 hover:border-amber/40'"
                            @click="selectedPlanId = plan.id"
                        >
                            <div>
                                <div class="font-black text-text-primary">{{ plan.name }}</div>
                                <div class="mt-1 text-xs font-black uppercase tracking-[0.2em] text-text-muted">
                                    {{ plan.type }} edition
                                    <span v-if="plan.billing_period"> · {{ plan.billing_period }} days</span>
                                </div>
                            </div>
                            <div class="text-xl font-black text-text-primary">
                                {{ formatMoney(plan.amount, plan.currency) }}
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Payment gateway selection -->
                <div class="mt-6">
                    <p class="mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Payment method</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <button
                            v-for="option in gatewayOptions"
                            :key="option.id"
                            type="button"
                            class="rounded-[24px] border p-5 text-left transition-colors"
                            :class="selectedGateway === option.id ? 'border-amber bg-amber/10' : 'border-panel-line bg-panel-2 hover:border-amber/40'"
                            @click="selectedGateway = option.id"
                        >
                            <div class="font-black text-text-primary">{{ option.label }}</div>
                            <div class="mt-1 text-xs text-text-muted">{{ option.desc }}</div>
                        </button>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <Button type="button" variant="secondary" @click="closeRenew">Cancel</Button>
                    <Button
                        type="button"
                        variant="primary"
                        :disabled="submittingRenewal || (renewingLicense?.renew_plans?.length ?? 0) === 0"
                        @click="submitRenewal"
                    >
                        <template #icon-after>
                            <LoaderCircle v-if="submittingRenewal" class="h-4 w-4 animate-spin" />
                        </template>
                        Continue to payment
                    </Button>
                </div>

                <p class="mt-4 text-[11px] text-text-muted">
                    Total:
                    <span class="font-black text-text-primary">
                        {{ selectedPlan ? formatMoney(selectedPlan.amount, selectedPlan.currency) : '—' }}
                    </span>
                </p>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
