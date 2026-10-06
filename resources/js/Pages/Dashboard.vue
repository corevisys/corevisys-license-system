<script setup>
import Alert from '@/Components/UI/Alert.vue';
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import TableCell from '@/Components/UI/TableCell.vue';
import TableHead from '@/Components/UI/TableHead.vue';
import TableHeaderCell from '@/Components/UI/TableHeaderCell.vue';
import TableRow from '@/Components/UI/TableRow.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: false,
        default: () => ({ active_count: 0, expiring_count: 0, total_spent: 0 }),
    },
    loading: {
        type: Boolean,
        default: false,
    },
    recentLicenses: {
        type: Array,
        default: () => [],
    },
});

const displayActive = ref(0);
const displayExpiring = ref(0);
const displaySpent = ref(0);

const animateValue = (target, refVar, duration = 1500) => {
    let start = 0;
    const end = parseInt(target);
    if (start === end) return;

    const range = end - start;
    let current = start;
    const increment = end > start ? 1 : -1;
    const stepTime = Math.abs(Math.floor(duration / range));

    const timer = setInterval(() => {
        current += increment;
        refVar.value = current;
        if (current == end) {
            clearInterval(timer);
        }
    }, Math.max(stepTime, 20));
};

const licenseStatus = (status) => {
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

onMounted(() => {
    if (!props.loading) {
        animateValue(props.stats.active_count, displayActive);
        animateValue(props.stats.expiring_count, displayExpiring);
        animateValue(props.stats.total_spent, displaySpent);
    }
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="mb-12 space-y-4">
            <Badge status="success" class="!rounded-full px-3 py-1.5">System overview</Badge>
            <h2 class="text-4xl font-black tracking-tight text-text-primary">
                Welcome back,
                <span class="text-amber">{{ $page.props.auth.user.name.split(' ')[0] }}</span>.
            </h2>
            <p v-if="!loading" class="text-sm text-text-muted">
                You have
                <span class="font-semibold text-text-secondary">{{ stats.active_count }} active licenses</span>
                under your account.
            </p>
            <div v-else class="h-5 w-64 animate-pulse rounded-full bg-panel-2" />
        </div>

        <Alert
            v-if="$page.props.flash?.new_license_key || $page.props.flash?.new_api_token"
            variant="success"
            class="mb-12"
        >
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <span class="text-[10px] font-black uppercase tracking-[0.24em] text-amber">New purchase</span>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div v-if="$page.props.flash?.new_license_key">
                        <p class="mb-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">License key</p>
                        <code class="block select-all rounded-xl border border-panel-line bg-bg-dark px-4 py-2 font-mono text-sm text-teal">
                            {{ $page.props.flash.new_license_key }}
                        </code>
                    </div>

                    <div v-if="$page.props.flash?.new_api_token">
                        <p class="mb-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">API token</p>
                        <code class="block select-all rounded-xl border border-panel-line bg-bg-dark px-4 py-2 font-mono text-sm text-amber">
                            {{ $page.props.flash.new_api_token }}
                        </code>
                    </div>
                </div>
            </div>
        </Alert>

        <div class="mb-16 grid gap-8 md:grid-cols-3">
            <template v-if="loading">
                <Card v-for="i in 3" :key="i" class="animate-pulse">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-panel-line" />
                        <div class="h-4 w-24 rounded-full bg-panel-line" />
                    </div>
                    <div class="h-10 w-16 rounded-xl bg-panel-line" />
                </Card>
            </template>

            <template v-else>
                <Card>
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        </div>
                        <p class="text-sm font-black uppercase tracking-[0.2em] text-text-muted">Active licenses</p>
                    </div>
                    <div class="flex items-end justify-between gap-3">
                        <p class="text-5xl font-black tracking-tight text-text-primary">{{ displayActive }}</p>
                        <Link href="/licenses" class="rounded-lg bg-teal/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-teal">View all</Link>
                    </div>
                </Card>

                <Card>
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber/10 text-amber">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <p class="text-sm font-black uppercase tracking-[0.2em] text-text-muted">Expiring soon</p>
                    </div>
                    <div class="flex items-end justify-between gap-3">
                        <p class="text-5xl font-black tracking-tight text-text-primary" :class="displayExpiring > 0 ? 'text-amber' : ''">{{ displayExpiring }}</p>
                        <Badge v-if="displayExpiring > 0" status="amber">Action required</Badge>
                    </div>
                </Card>

                <Card>
                    <div class="mb-6 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber/10 text-amber">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <p class="text-sm font-black uppercase tracking-[0.2em] text-text-muted">Total investment</p>
                    </div>
                    <div class="flex items-end justify-between gap-3">
                        <p class="text-5xl font-black tracking-tight text-text-primary">${{ displaySpent }}</p>
                        <Link href="/orders" class="rounded-lg bg-panel-line px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.2em] text-text-secondary">History</Link>
                    </div>
                </Card>
            </template>
        </div>

        <div class="mb-8 flex items-center justify-between">
            <h3 class="text-xl font-black tracking-tight text-text-primary">Recent activity</h3>
            <Button type="button" variant="secondary">System updates</Button>
        </div>

        <Card class="overflow-hidden p-0">
            <div v-if="!recentLicenses || recentLicenses.length === 0" class="p-16 text-center">
                <TableEmpty>No licenses found yet. Your recently purchased licenses will appear here.</TableEmpty>
            </div>

            <div v-else class="overflow-x-auto">
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeaderCell>Product</TableHeaderCell>
                            <TableHeaderCell>License ID</TableHeaderCell>
                            <TableHeaderCell>Status</TableHeaderCell>
                            <TableHeaderCell>Expiry</TableHeaderCell>
                            <TableHeaderCell class="text-right">Actions</TableHeaderCell>
                        </TableRow>
                    </TableHead>
                    <tbody>
                        <TableRow v-for="license in recentLicenses" :key="license.id">
                            <TableCell>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal/10 text-teal">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    </div>
                                    <span class="font-semibold text-text-primary">{{ license.product.name }}</span>
                                </div>
                            </TableCell>
                            <TableCell mono>
                                XXXX-XXXX-XXXX-{{ license.license_key_hash.substring(0, 4) }}
                            </TableCell>
                            <TableCell>
                                <Badge :status="licenseStatus(license.status)">{{ license.status }}</Badge>
                            </TableCell>
                            <TableCell>
                                {{ license.expires_at ? new Date(license.expires_at).toLocaleDateString() : 'Lifetime' }}
                            </TableCell>
                            <TableCell class="text-right">
                                <Link :href="route('licenses')" class="text-[10px] font-black uppercase tracking-[0.2em] text-teal hover:underline">Manage</Link>
                            </TableCell>
                        </TableRow>
                    </tbody>
                </Table>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
