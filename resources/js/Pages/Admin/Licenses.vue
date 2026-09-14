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
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps({
    licenses: Array,
});

const search = ref('');
const statusFilter = ref('all');

const filteredLicenses = computed(() => {
    return props.licenses.filter((license) => {
        const value = String(license.status || '').toLowerCase();
        const matchesStatus = statusFilter.value === 'all' || value === statusFilter.value;
        const matchesSearch =
            license.user_name.toLowerCase().includes(search.value.toLowerCase()) ||
            license.user_email.toLowerCase().includes(search.value.toLowerCase()) ||
            license.product_name.toLowerCase().includes(search.value.toLowerCase());

        return matchesStatus && matchesSearch;
    });
});

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

let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({
            only: ['licenses'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 30000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <Head title="License Management" />

    <AuthenticatedLayout>
        <div class="mb-12 flex items-center justify-between gap-4">
            <div>
                <Badge status="success" class="!rounded-full px-3 py-1.5">License control</Badge>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">License management</h2>
            </div>
            <div class="relative">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search user or product..."
                    class="w-64 rounded-xl border border-panel-line bg-panel-2 py-2 pl-10 pr-4 text-sm text-text-primary placeholder:text-text-muted focus:border-amber focus:outline-none"
                >
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>

        <div class="mb-6 flex flex-wrap gap-2">
            <Button v-for="status in ['all', 'active', 'inactive', 'expired', 'suspended', 'revoked']" :key="status" type="button" :variant="statusFilter === status ? 'primary' : 'secondary'" @click="statusFilter = status">
                {{ status }}
            </Button>
        </div>

        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeaderCell>User / product</TableHeaderCell>
                            <TableHeaderCell>Security key</TableHeaderCell>
                            <TableHeaderCell class="text-center">Live</TableHeaderCell>
                            <TableHeaderCell>Status / type</TableHeaderCell>
                            <TableHeaderCell>Expires / pulse</TableHeaderCell>
                            <TableHeaderCell class="text-right">Actions</TableHeaderCell>
                        </TableRow>
                    </TableHead>
                    <tbody>
                        <TableRow v-for="license in filteredLicenses" :key="license.id">
                            <TableCell>
                                <div class="font-bold text-text-primary">{{ license.product_name }}</div>
                                <div class="text-xs text-text-muted">{{ license.user_name }} ({{ license.user_email }})</div>
                            </TableCell>
                            <TableCell>
                                <code class="rounded-lg border border-panel-line bg-panel-2 px-2 py-1 font-mono text-xs text-teal">{{ license.key_preview }}</code>
                            </TableCell>
                            <TableCell class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <span class="h-2 w-2 rounded-full" :class="license.is_running ? 'bg-teal' : 'bg-panel-line'" />
                                    <span class="text-[10px] font-bold uppercase tracking-[0.2em]" :class="license.is_running ? 'text-teal' : 'text-text-muted'">
                                        {{ license.is_running ? 'Live' : 'Idle' }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col gap-1">
                                    <Badge :status="getStatusColor(license.status)">{{ license.status }}</Badge>
                                    <span class="ml-1 text-[10px] font-bold uppercase tracking-[0.2em] text-text-muted">{{ license.type }}</span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-col">
                                    <span class="mb-1 text-xs text-text-muted">{{ license.expires_at }}</span>
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">
                                        Last pulse: {{ license.last_check_at ? license.last_check_at.split(' ')[1] : 'Never' }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <Link :href="route('admin.licenses.show', license.id)" class="inline-flex items-center gap-2 rounded-xl border border-panel-line bg-panel-2 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-secondary transition hover:border-amber hover:text-amber">
                                    Details
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                                </Link>
                            </TableCell>
                        </TableRow>
                    </tbody>
                </Table>
                <div v-if="filteredLicenses.length === 0" class="py-20 text-center text-sm font-bold uppercase tracking-[0.2em] text-text-muted">
                    No licenses found matching your criteria.
                </div>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
