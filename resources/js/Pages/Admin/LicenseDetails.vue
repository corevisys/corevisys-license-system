<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import TableCell from '@/Components/UI/TableCell.vue';
import TableHead from '@/Components/UI/TableHead.vue';
import TableHeaderCell from '@/Components/UI/TableHeaderCell.vue';
import TableRow from '@/Components/UI/TableRow.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    license: Object,
});

const updateStatus = (status) => {
    const msg = status === 'revoked'
        ? 'WARNING: This is KILL MODE. The project will wipe all local license data and shutdown immediately. Proceed?'
        : `Are you sure you want to set this license to ${status}?`;

    if (confirm(msg)) {
        router.post(route('admin.licenses.status', props.license.id), { status });
    }
};

const resetBinding = () => {
    if (confirm('Are you sure you want to reset bindings for this license? The user will need to reactivate on their next run.')) {
        router.post(route('admin.licenses.reset-binding', props.license.id));
    }
};

const getStatusColor = (status) => {
    const value = String(status || '').toLowerCase();
    const mapping = {
        active: 'success',
        inactive: 'default',
        expired: 'amber',
        suspended: 'danger',
        revoked: 'danger',
    };

    return mapping[value] ?? 'default';
};
</script>

<template>
    <Head :title="'License Details - ' + license.product_name" />

    <AuthenticatedLayout>
        <div class="mb-8 flex items-center gap-4">
            <Link :href="route('admin.licenses')" class="rounded-xl border border-panel-line bg-panel-2 p-2 text-text-muted transition hover:border-amber hover:text-amber">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
            </Link>
            <h2 class="text-xl font-black uppercase tracking-[0.2em] text-text-primary">License <span class="text-teal">details</span></h2>
        </div>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                <Card class="p-8">
                    <div class="mb-8 flex items-start justify-between gap-4">
                        <div>
                            <h3 class="mb-2 text-2xl font-black text-text-primary">{{ license.product_name }}</h3>
                            <p class="text-sm font-bold uppercase tracking-[0.2em] text-text-muted">{{ license.type }} edition</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <Badge :status="license.is_running ? 'success' : 'default'">{{ license.is_running ? 'Live' : 'Idle' }}</Badge>
                            <Badge :status="getStatusColor(license.status)">{{ license.status }}</Badge>
                        </div>
                    </div>

                    <div class="mb-8 flex items-center justify-between rounded-2xl border border-panel-line bg-panel-2 p-4">
                        <div class="flex flex-col">
                            <span class="mb-1 text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Last heartbeat link</span>
                            <span class="flex items-center gap-2 text-xs font-bold text-text-primary">
                                <svg class="h-3.5 w-3.5 text-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                {{ license.last_check_at || 'Never established' }}
                            </span>
                        </div>
                        <div v-if="license.is_running" class="text-[9px] font-black uppercase tracking-[0.2em] text-teal">
                            Verified connection active
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-6">
                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">License key</label>
                                <code class="block break-all rounded-2xl border border-panel-line bg-panel-2 px-4 py-3 font-mono text-sm text-teal">{{ license.license_key }}</code>
                            </div>
                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Valid until</label>
                                <p class="font-bold text-text-primary">{{ license.expires_at }}</p>
                            </div>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Assigned user</label>
                                <div class="flex flex-col">
                                    <span class="font-bold text-text-primary">{{ license.user_name }}</span>
                                    <span class="text-xs text-text-muted">{{ license.user_email }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Enforcement mode</label>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-text-primary">{{ license.enforcement_mode }}</p>
                            </div>
                        </div>
                    </div>
                </Card>

                <Card class="p-8">
                    <h3 class="mb-6 flex items-center gap-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">
                        <span class="h-1.5 w-1.5 rounded-full bg-teal" />
                        Management actions
                    </h3>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <button v-if="license.status !== 'active'" @click="updateStatus('active')" class="flex items-center justify-between rounded-2xl border border-teal/35 bg-teal/8 p-4 text-left text-teal transition hover:bg-teal hover:text-[#071510]">
                            <div>
                                <div class="text-xs font-black uppercase tracking-[0.2em]">Activate</div>
                                <div class="mt-1 text-[10px] text-current/75">Restore full access to this license</div>
                            </div>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </button>

                        <button v-if="license.status !== 'suspended'" @click="updateStatus('suspended')" class="flex items-center justify-between rounded-2xl border border-danger/35 bg-danger/8 p-4 text-left text-danger transition hover:bg-danger hover:text-[#2A0F0C]">
                            <div>
                                <div class="text-xs font-black uppercase tracking-[0.2em]">Suspend</div>
                                <div class="mt-1 text-[10px] text-current/75">Temporarily block application usage</div>
                            </div>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        </button>

                        <button v-if="license.status !== 'revoked'" @click="updateStatus('revoked')" class="flex items-center justify-between rounded-2xl border border-danger/35 bg-danger/8 p-4 text-left text-danger transition hover:bg-danger hover:text-[#2A0F0C]">
                            <div>
                                <div class="text-xs font-black uppercase tracking-[0.2em]">Revoke (kill mode)</div>
                                <div class="mt-1 text-[10px] text-current/75">Wipe local data & permanent shutdown</div>
                            </div>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </button>

                        <button v-if="license.bound_domain || license.bound_ip" @click="resetBinding" class="flex items-center justify-between rounded-2xl border border-panel-line bg-panel-2 p-4 text-left text-text-secondary transition hover:border-amber hover:text-amber">
                            <div>
                                <div class="text-xs font-black uppercase tracking-[0.2em]">Reset bindings</div>
                                <div class="mt-1 text-[10px] text-text-muted">Clear domain/IP and allow new activation</div>
                            </div>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        </button>
                    </div>
                </Card>
            </div>

            <div class="space-y-8">
                <Card class="p-8">
                    <h3 class="mb-6 flex items-center gap-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">
                        <svg class="h-4 w-4 text-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
                        Environment binding
                    </h3>

                    <div class="space-y-4">
                        <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Bound domain</label>
                            <p class="font-mono text-sm text-text-primary">{{ license.bound_domain || 'NOT BOUND' }}</p>
                        </div>
                        <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Bound IP address</label>
                            <p class="font-mono text-sm text-text-primary">{{ license.bound_ip || 'NOT BOUND' }}</p>
                        </div>
                        <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                            <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Activation limit</label>
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-black text-text-primary">{{ license.current_usage }} / {{ license.activation_limit }}</p>
                                <div class="ml-4 h-1 flex-1 overflow-hidden rounded-full bg-panel-line">
                                    <div class="h-full bg-teal transition-all duration-1000" :style="{ width: (license.activation_limit > 0 ? (license.current_usage / license.activation_limit * 100) : 0) + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <Card class="p-8">
                    <h3 class="mb-6 flex items-center gap-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">
                        <svg class="h-4 w-4 text-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Recent pulse activity
                    </h3>

                    <div class="space-y-4">
                        <div v-for="log in license.history.slice(0, 5)" :key="log.id" class="flex items-start gap-4 rounded-xl border border-panel-line bg-panel-2 p-3">
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" :class="log.status === 'pulse' ? 'bg-teal' : 'bg-amber'" />
                            <div class="flex-1">
                                <div class="mb-1 flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-[0.2em] text-text-primary">{{ log.status }}</span>
                                    <span class="text-[9px] text-text-muted">{{ log.created_at }}</span>
                                </div>
                                <div class="text-[10px] font-mono text-text-muted">{{ log.request_domain }}</div>
                            </div>
                        </div>
                        <div v-if="license.history.length === 0" class="py-4 text-center text-xs italic text-text-muted">
                            No activity recorded.
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <Card class="mt-8 overflow-hidden p-0">
            <div class="border-b border-panel-line p-6">
                <h3 class="text-sm font-black uppercase tracking-[0.2em] text-text-primary">Detailed verification log</h3>
            </div>
            <div class="overflow-x-auto">
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeaderCell>Timestamp</TableHeaderCell>
                            <TableHeaderCell>Domain</TableHeaderCell>
                            <TableHeaderCell>IP address</TableHeaderCell>
                            <TableHeaderCell>Event type</TableHeaderCell>
                        </TableRow>
                    </TableHead>
                    <tbody>
                        <TableRow v-for="log in license.history" :key="log.id">
                            <TableCell>{{ log.created_at }}</TableCell>
                            <TableCell mono>{{ log.request_domain }}</TableCell>
                            <TableCell mono>{{ log.request_ip }}</TableCell>
                            <TableCell>
                                <Badge :status="log.status === 'pulse' ? 'success' : 'default'">{{ log.status }}</Badge>
                            </TableCell>
                        </TableRow>
                    </tbody>
                </Table>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
