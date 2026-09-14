<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Modal from '@/Components/Modal.vue';
import Table from '@/Components/UI/Table.vue';
import TableCell from '@/Components/UI/TableCell.vue';
import TableHead from '@/Components/UI/TableHead.vue';
import TableHeaderCell from '@/Components/UI/TableHeaderCell.vue';
import TableRow from '@/Components/UI/TableRow.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

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
});

const copyKey = (key) => {
    if (!key) return;
    navigator.clipboard.writeText(key);
    alert('License key copied to clipboard!');
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

const handleUpgrade = (license, priceId) => {
    router.post(route('licenses.upgrade', license.id), {
        product_price_id: priceId,
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

let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({
            only: ['licenses'],
            preserveScroll: true,
            preserveState: true,
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
        <div class="mb-12 flex items-center justify-between gap-4">
            <div>
                <Badge status="success" class="!rounded-full px-3 py-1.5">Asset vault</Badge>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">My assets</h2>
                <p class="mt-2 text-sm text-text-muted">Manage your active software seats and domain bindings.</p>
            </div>
            <Button type="button" variant="primary" @click="router.get(route('store'))">New license</Button>
        </div>

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
                                    <button @click="copyKey(license.full_key)" class="rounded-xl border border-panel-line p-2 text-text-muted transition hover:border-amber hover:text-amber" title="Secure copy">
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
                                    <Button v-if="license.upgrade_plans && license.upgrade_plans.length > 0" type="button" variant="primary" @click="upgradingLicense = license">Upgrade</Button>
                                    <Button v-if="license.can_renew" type="button" variant="secondary" @click="handleRenew(license)">Renew</Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </tbody>
                </Table>
            </div>
        </Card>

        <Modal :show="!!upgradingLicense" @close="upgradingLicense = null">
            <div class="p-8">
                <h3 class="mb-6 text-2xl font-black text-text-primary">Upgrade your license</h3>
                <div class="space-y-4">
                    <div v-for="plan in upgradingLicense?.upgrade_plans" :key="plan.id" class="flex items-center justify-between rounded-[24px] border border-panel-line bg-panel-2 p-6">
                        <div>
                            <div class="font-black text-text-primary">{{ plan.name }}</div>
                            <div class="mt-1 text-xs font-black uppercase tracking-[0.2em] text-text-muted">{{ plan.type }} edition</div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-xl font-black text-text-primary">${{ plan.amount }}</div>
                            <Button type="button" variant="primary" @click="handleUpgrade(upgradingLicense, plan.id)">Select</Button>
                        </div>
                    </div>
                </div>
                <div class="mt-8 flex justify-end">
                    <Button type="button" variant="secondary" @click="upgradingLicense = null">Cancel</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
