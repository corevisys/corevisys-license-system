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
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    orders: Array,
});

const search = ref('');
const statusFilter = ref('pending');

const filteredOrders = computed(() => {
    return props.orders.filter((order) => {
        const value = String(order.status || '').toLowerCase();
        const matchesStatus = statusFilter.value === 'all' || value === statusFilter.value;
        const matchesSearch =
            order.order_number.toLowerCase().includes(search.value.toLowerCase()) ||
            order.user_email.toLowerCase().includes(search.value.toLowerCase()) ||
            (order.transaction_id && order.transaction_id.toLowerCase().includes(search.value.toLowerCase()));

        return matchesStatus && matchesSearch;
    });
});

const verifyOrder = (id) => {
    if (confirm('Are you sure you want to verify this order?')) {
        router.post(route('admin.orders.verify', id));
    }
};

const getStatusColor = (status) => {
    const value = String(status || '').toLowerCase();
    const mapping = {
        pending: 'amber',
        awaiting_payment: 'amber',
        completed: 'success',
        cancelled: 'danger',
    };

    return mapping[value] ?? 'default';
};
</script>

<template>
    <Head title="Order Management" />

    <AuthenticatedLayout>
        <div class="mb-12 flex items-center justify-between gap-4">
            <div>
                <Badge status="success" class="!rounded-full px-3 py-1.5">Order queue</Badge>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">Order management</h2>
            </div>
            <div class="relative">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search orders..."
                    class="w-64 rounded-xl border border-panel-line bg-panel-2 py-2 pl-10 pr-4 text-sm text-text-primary placeholder:text-text-muted focus:border-amber focus:outline-none"
                >
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
        </div>

        <div class="mb-6 flex flex-wrap gap-2">
            <Button v-for="status in ['pending', 'awaiting_payment', 'completed', 'cancelled', 'all']" :key="status" type="button" :variant="statusFilter === status ? 'primary' : 'secondary'" @click="statusFilter = status">
                {{ status === 'all' ? 'All' : status.replace('_', ' ') }}
            </Button>
        </div>

        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeaderCell>Order ID</TableHeaderCell>
                            <TableHeaderCell>User</TableHeaderCell>
                            <TableHeaderCell>Amount</TableHeaderCell>
                            <TableHeaderCell>Method</TableHeaderCell>
                            <TableHeaderCell>Trx ID</TableHeaderCell>
                            <TableHeaderCell>Status</TableHeaderCell>
                            <TableHeaderCell class="text-right">Actions</TableHeaderCell>
                        </TableRow>
                    </TableHead>
                    <tbody>
                        <TableRow v-for="order in filteredOrders" :key="order.id">
                            <TableCell>
                                <span class="font-mono text-xs text-teal">{{ order.order_number }}</span>
                                <div class="text-[10px] text-text-muted">{{ order.created_at }}</div>
                            </TableCell>
                            <TableCell>
                                <div class="font-bold text-text-primary">{{ order.user_name }}</div>
                                <div class="text-xs text-text-muted">{{ order.user_email }}</div>
                            </TableCell>
                            <TableCell>
                                <span class="font-bold text-text-primary">{{ order.currency }} {{ order.total_amount }}</span>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2">
                                    <template v-if="order.payment_method === 'online'">
                                        <span class="h-2.5 w-2.5 rounded-full bg-teal" />
                                        <span class="text-xs font-bold uppercase tracking-[0.2em] text-text-secondary">Stripe</span>
                                    </template>
                                    <template v-else>
                                        <span class="h-2.5 w-2.5 rounded-full bg-panel-line" />
                                        <span class="text-xs font-medium uppercase tracking-[0.2em] text-text-muted">Manual</span>
                                    </template>
                                </div>
                            </TableCell>
                            <TableCell>
                                <span class="font-mono text-xs text-text-muted">{{ order.transaction_id }}</span>
                            </TableCell>
                            <TableCell>
                                <Badge :status="getStatusColor(order.status)">{{ order.status }}</Badge>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button v-if="order.status === 'pending'" type="button" variant="primary" @click="verifyOrder(order.id)">Verify</Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="filteredOrders.length === 0">
                            <TableCell colspan="7" class="py-12 text-center text-sm text-text-muted">No orders found.</TableCell>
                        </TableRow>
                    </tbody>
                </Table>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
