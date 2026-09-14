<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Card from '@/Components/UI/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            active_licenses: 0,
            total_revenue: 0,
            avg_uptime: 99.99,
            server_load: 0,
        }),
    },
    charts: {
        type: Object,
        default: () => ({
            distribution: {},
            orders_trend: [0, 0, 0, 0, 0, 0],
            months: ['J', 'F', 'M', 'A', 'M', 'J'],
            status_summary: {},
        }),
    },
});

const displayRevenue = ref(0);
const loadWidth = ref(0);

const animateValue = (target, refVar, duration = 1500) => {
    let start = 0;
    const end = parseFloat(target);
    if (isNaN(end)) return;
    const range = end - start;
    if (range === 0) {
        refVar.value = end;
        return;
    }
    let current = start;

    const timer = setInterval(() => {
        current += range / (duration / 20);
        if ((range > 0 && current >= end) || (range < 0 && current <= end)) {
            refVar.value = end;
            clearInterval(timer);
        } else {
            refVar.value = current;
        }
    }, 20);
};

onMounted(() => {
    animateValue(props.stats.total_revenue, displayRevenue);
    setTimeout(() => {
        loadWidth.value = props.stats.server_load;
    }, 500);
});
</script>

<template>
    <Head title="Performance Analytics" />

    <AuthenticatedLayout>
        <div class="mb-12 space-y-4">
            <Badge status="success" class="!rounded-full px-3 py-1.5">Operational analytics</Badge>
            <h2 class="text-4xl font-black tracking-tight text-text-primary">
                Operational <span class="text-amber">Analytics</span>
            </h2>
            <p class="text-sm text-text-muted">Deep insights into your license fleet and system health.</p>
        </div>

        <div class="mb-8 grid gap-8 lg:grid-cols-2">
            <Card class="overflow-hidden">
                <div class="mb-8 flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">Expenditure trend</p>
                        <h4 class="text-2xl font-black text-text-primary">
                            ${{ displayRevenue.toLocaleString() }}
                            <span class="ml-2 text-[10px] font-bold italic tracking-normal text-text-muted">Total</span>
                        </h4>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                </div>

                <div class="flex h-48 items-end gap-2 px-2">
                    <div
                        v-for="(val, index) in charts.orders_trend"
                        :key="index"
                        class="group/bar relative flex-1 rounded-t-lg bg-gradient-to-t from-teal/20 to-teal transition-all duration-1000"
                        :style="{
                            height: `${Math.max((val / Math.max(...charts.orders_trend, 1)) * 100, 5)}%`,
                            opacity: 0.4 + index * 0.1,
                        }"
                    >
                        <div class="pointer-events-none absolute -top-8 left-1/2 -translate-x-1/2 rounded border border-panel-line bg-panel-2 px-2 py-1 text-[10px] font-black opacity-0 transition-opacity group-hover/bar:opacity-100">
                            ${{ val }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-between px-2 text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">
                    <span v-for="month in charts.months" :key="month">{{ month }}</span>
                </div>
            </Card>

            <Card>
                <div class="mb-10 flex items-center justify-between">
                    <div>
                        <p class="mb-1 text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">License fleet</p>
                        <h4 class="text-2xl font-black text-text-primary">{{ stats.active_licenses }} active nodes</h4>
                    </div>
                    <Badge status="success">Live analytics</Badge>
                </div>

                <div class="space-y-8">
                    <div v-if="Object.keys(charts.distribution).length > 0">
                        <div v-for="(count, product) in charts.distribution" :key="product" class="mb-4 last:mb-0">
                            <div class="mb-2 flex items-center justify-between text-xs font-bold uppercase tracking-[0.2em] text-text-muted">
                                <span>{{ product }}</span>
                                <span class="text-teal">{{ count }} units</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-panel-line">
                                <div
                                    class="h-full bg-gradient-to-r from-amber to-teal transition-all duration-1000"
                                    :style="{ width: `${(count / Object.values(charts.distribution).reduce((a, b) => a + b, 0)) * 100}%` }"
                                />
                            </div>
                        </div>
                    </div>
                    <div v-else class="py-10 text-center text-sm text-text-muted">
                        No license data found.
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-panel-line pt-8">
                        <div class="rounded-3xl border border-panel-line bg-panel-2 p-5 text-center">
                            <p class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-text-muted">Uptime</p>
                            <p class="text-2xl font-black text-text-primary">{{ stats.avg_uptime }}%</p>
                        </div>
                        <div class="rounded-3xl border border-panel-line bg-panel-2 p-5 text-center">
                            <p class="mb-2 text-xs font-black uppercase tracking-[0.2em] text-text-muted">System load</p>
                            <p class="text-2xl font-black" :class="stats.server_load > 80 ? 'text-danger' : 'text-amber'">{{ stats.server_load }}%</p>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
            <Card class="flex items-center gap-6">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber/10 text-amber">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h5 class="mb-1 text-xs font-black uppercase tracking-[0.2em] text-text-muted">Expiring soon</h5>
                    <p class="text-xl font-black text-text-primary">{{ charts.status_summary.expiring || 0 }}</p>
                </div>
            </Card>

            <Card class="flex items-center gap-6">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-danger/10 text-danger">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h5 class="mb-1 text-xs font-black uppercase tracking-[0.2em] text-text-muted">Expired / revoked</h5>
                    <p class="text-xl font-black text-text-primary">{{ (charts.status_summary.expired || 0) + (charts.status_summary.revoked || 0) }}</p>
                </div>
            </Card>

            <Card class="flex items-center gap-6">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h5 class="mb-1 text-xs font-black uppercase tracking-[0.2em] text-text-muted">Total assets</h5>
                    <p class="text-xl font-black text-text-primary">{{ Object.values(charts.status_summary).reduce((a, b) => a + b, 0) }}</p>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
