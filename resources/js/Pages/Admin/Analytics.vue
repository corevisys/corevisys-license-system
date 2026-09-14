<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    stats: Object,
    revenue_trend: Array,
    recent_activities: Object,
});

let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({
            only: ['stats', 'revenue_trend', 'recent_activities'],
            preserveScroll: true,
            preserveState: true,
        });
    }, 30000);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

const revenueChartPath = computed(() => {
    if (!props.revenue_trend || props.revenue_trend.length === 0) return '';

    const data = props.revenue_trend.map(Number);
    const max = Math.max(...data, 1);
    const min = 0;
    const width = 100;
    const height = 100;
    const stepX = width / (data.length - 1);

    const points = data.map((val, index) => {
        const x = index * stepX;
        const y = height - ((val - min) / (max - min)) * height;
        return `${x},${y}`;
    });

    return `M0,${height} L${points.join(' L')} L${width},${height} Z`;
});
</script>

<template>
    <Head title="Admin Analytics" />

    <AuthenticatedLayout>
        <div class="mb-12 flex items-center justify-between gap-4">
            <div>
                <Badge status="success" class="!rounded-full px-3 py-1.5">System analytics</Badge>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">System analytics</h2>
                <p class="mt-2 text-sm text-text-muted">Detailed performance metrics and historical data.</p>
            </div>
            <Button type="button" variant="secondary">Export report</Button>
        </div>

        <div class="mb-16 grid grid-cols-1 gap-8 md:grid-cols-4">
            <Card class="flex flex-col items-center p-8 text-center">
                <span class="mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Total revenue</span>
                <p class="mb-4 text-4xl font-black text-text-primary">${{ stats.total_revenue.toLocaleString() }}</p>
                <Badge status="success">Live</Badge>
            </Card>

            <Card class="flex flex-col items-center p-8 text-center">
                <span class="mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Active licenses</span>
                <p class="mb-2 text-4xl font-black text-text-primary">{{ stats.active_licenses }}</p>
                <div class="flex gap-2">
                    <Badge status="default">Prob: {{ stats.trial_licenses }}</Badge>
                    <Badge status="default">Sub: {{ stats.subscription_licenses }}</Badge>
                </div>
            </Card>

            <Card class="flex flex-col items-center p-8 text-center">
                <span class="mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Pending orders</span>
                <p class="mb-4 text-4xl font-black text-amber">{{ stats.pending_orders }}</p>
                <Badge status="amber">Requires action</Badge>
            </Card>

            <Card class="flex flex-col items-center p-8 text-center">
                <span class="mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Currently running</span>
                <p class="mb-4 text-4xl font-black text-teal">{{ stats.running_projects }}</p>
                <Badge status="success">Live connections</Badge>
            </Card>
        </div>

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
            <Card class="p-8">
                <div class="mb-8 flex items-center justify-between">
                    <h3 class="text-lg font-black tracking-tight text-text-primary">Revenue scale (30 days)</h3>
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-teal" />
                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-text-muted">Calculated trajectory</span>
                    </div>
                </div>
                <div class="relative h-72 w-full overflow-hidden rounded-[24px] border border-panel-line bg-panel-2">
                    <svg class="absolute inset-0 h-full w-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                        <path :d="revenueChartPath" fill="url(#brand-grad-analytics)" class="transition-all duration-1000 ease-in-out" />
                        <defs>
                            <linearGradient id="brand-grad-analytics" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#4F46E5" stop-opacity="0.6" />
                                <stop offset="100%" stop-color="#4F46E5" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </Card>

            <Card class="p-8">
                <div class="mb-8 flex items-center justify-between">
                    <h3 class="text-lg font-black tracking-tight text-text-primary">Security intelligence</h3>
                </div>
                <div class="space-y-3">
                    <div v-for="activity in recent_activities.data" :key="activity.id" class="flex items-center justify-between rounded-2xl border border-panel-line bg-panel-2 p-4 transition hover:border-amber/25">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl"
                                :class="{
                                    'bg-teal/10 text-teal': activity.status === 'success',
                                    'bg-danger/8 text-danger': activity.status !== 'success'
                                }">
                                <svg v-if="activity.status === 'success'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-black leading-tight text-text-primary">{{ activity.product_name }}</p>
                                <p class="text-[11px] font-medium text-text-muted">Domain: {{ activity.domain }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em]" :class="activity.status === 'success' ? 'text-teal' : 'text-danger'">
                                {{ activity.status }}
                            </span>
                            <span class="text-[9px] text-text-muted">{{ activity.created_at }}</span>
                        </div>
                    </div>
                    <div v-if="recent_activities.data.length === 0" class="py-8 text-center text-sm text-text-muted opacity-50">
                        No recent activity detected.
                    </div>
                </div>

                <div v-if="recent_activities.links && recent_activities.links.length > 3" class="mt-6 flex items-center justify-between">
                    <div class="flex gap-2">
                        <Link v-if="recent_activities.prev_page_url" :href="recent_activities.prev_page_url" preserve-scroll class="inline-flex items-center rounded-xl border border-panel-line bg-panel-2 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-secondary transition hover:border-amber hover:text-amber">
                            Previous
                        </Link>
                        <button v-else disabled class="cursor-not-allowed rounded-xl border border-panel-line bg-panel-2 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted/60">
                            Previous
                        </button>

                        <Link v-if="recent_activities.next_page_url" :href="recent_activities.next_page_url" preserve-scroll class="inline-flex items-center rounded-xl border border-panel-line bg-panel-2 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-secondary transition hover:border-amber hover:text-amber">
                            Next
                        </Link>
                        <button v-else disabled class="cursor-not-allowed rounded-xl border border-panel-line bg-panel-2 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-text-muted/60">
                            Next
                        </button>
                    </div>
                    <span class="text-[10px] font-medium text-text-muted">
                        Page {{ recent_activities.current_page }} of {{ recent_activities.last_page }}
                    </span>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
