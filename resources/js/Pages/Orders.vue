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
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    orders: {
        type: Array,
        required: true,
    },
});

const orderStatusVariant = (status) => {
    const value = String(status || '').toLowerCase();
    const mapping = {
        pending: 'amber',
        awaiting_payment: 'amber',
        completed: 'success',
        cancelled: 'danger',
    };

    return mapping[value] ?? 'default';
};

const generateInvoice = (id) => {
    alert('Generating invoice for order #' + String(id).padStart(5, '0') + '... Your download will start shortly.');
};
</script>

<template>
    <Head title="Orders & Payments" />

    <AuthenticatedLayout>
        <div class="mb-12 space-y-4">
            <Badge status="success" class="!rounded-full px-3 py-1.5">Payment history</Badge>
            <h2 class="text-4xl font-black tracking-tight text-text-primary">History</h2>
            <p class="text-sm text-text-muted">Track your purchases and secure your invoices.</p>
        </div>

        <Card class="overflow-hidden p-0">
            <div class="overflow-x-auto">
                <Table>
                    <TableHead>
                        <TableRow>
                            <TableHeaderCell>Reference</TableHeaderCell>
                            <TableHeaderCell>Date issued</TableHeaderCell>
                            <TableHeaderCell>Value</TableHeaderCell>
                            <TableHeaderCell>Status</TableHeaderCell>
                            <TableHeaderCell>Method</TableHeaderCell>
                            <TableHeaderCell class="text-right">Actions</TableHeaderCell>
                        </TableRow>
                    </TableHead>
                    <tbody>
                        <TableRow v-for="order in orders" :key="order.id">
                            <TableCell mono>#{{ String(order.id).padStart(5, '0') }}</TableCell>
                            <TableCell>{{ order.created_at }}</TableCell>
                            <TableCell>
                                {{ order.amount }} <span class="text-[10px] uppercase tracking-[0.2em] text-text-muted">{{ order.currency }}</span>
                            </TableCell>
                            <TableCell>
                                <Badge :status="orderStatusVariant(order.status)">{{ order.status }}</Badge>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-secondary">
                                    <template v-if="order.gateway === 'bkash'">
                                        <span class="h-2.5 w-2.5 rounded-full bg-amber" />
                                        <span>bKash</span>
                                    </template>
                                    <template v-else-if="order.payment_method === 'online'">
                                        <span class="h-2.5 w-2.5 rounded-full bg-teal" />
                                        <span>Stripe</span>
                                    </template>
                                    <template v-else>
                                        <span class="h-2.5 w-2.5 rounded-full bg-panel-line" />
                                        <span>Manual</span>
                                    </template>
                                </div>
                            </TableCell>
                            <TableCell class="text-right">
                                <Button type="button" variant="secondary" @click="generateInvoice(order.id)">Invoice</Button>
                            </TableCell>
                        </TableRow>
                    </tbody>
                </Table>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
