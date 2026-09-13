<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    stats: Object,
    revenue_trend: Array,
    recent_activities: Object, // Changed from Array to Object for pagination
});

// Polling for Real-Time Updates
let pollInterval = null;

onMounted(() => {
    pollInterval = setInterval(() => {
        router.reload({
            only: ['stats', 'revenue_trend', 'recent_activities'],
            preserveScroll: true,
            preserveState: true
        });
    }, 30000); // 30 seconds for analytics
});
// ... (rest of logic unchanged) ...

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

// Chart Logic
const revenueChartPath = computed(() => {
    if (!props.revenue_trend || props.revenue_trend.length === 0) return '';
    
    // Normalize data
    const data = props.revenue_trend.map(Number);
    const max = Math.max(...data, 1); // Avoid division by zero
    const min = 0;
    
    // SVG Dimensions (Match the viewbox or container aspect ratio)
    const width = 100;
    const height = 100;
    
    const stepX = width / (data.length - 1);
    
    // Build path points
    const points = data.map((val, index) => {
        const x = index * stepX;
        const y = height - ((val - min) / (max - min)) * height;
        return `${x},${y}`;
    });

    // Close the area for fill effect (bottom-right -> bottom-left)
    return `M0,${height} L${points.join(' L')} L${width},${height} Z`;
});
</script>

<template>
    <Head title="Admin Analytics" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="mb-12 flex items-center justify-between">
            <div>
                <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">System Analytics</h2>
                <p class="text-text-muted font-medium italic">Detailed performance metrics and historical data.</p>
            </div>
            <div class="flex gap-4">
                <button class="px-6 py-3 bg-white/5 border border-white/10 rounded-2xl text-xs font-bold text-text-muted hover:bg-white/10 transition-all uppercase tracking-widest shadow-soft-md">Export Report</button>
            </div>
        </div>

        <!-- KPI Command Center -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-16">
            <div class="bg-bg-dark/50 backdrop-blur-md p-8 rounded-[32px] shadow-soft-md border border-white/5 flex flex-col items-center text-center">
                <span class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-4">Total Revenue</span>
                <p class="text-4xl font-black text-adaptive mb-4">${{ stats.total_revenue.toLocaleString() }}</p>
                <div class="flex items-center gap-1.5 px-3 py-1 bg-emerald-50 dark:bg-emerald-900/20 rounded-full">
                    <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7" /></svg>
                    <span class="text-[10px] font-black text-emerald-600 dark:text-emerald-400 tracking-tighter">Live</span>
                </div>
            </div>

            <div class="bg-bg-dark/50 backdrop-blur-md p-8 rounded-[32px] shadow-soft-md border border-white/5 flex flex-col items-center text-center">
                <span class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-4">Active Licenses</span>
                <p class="text-4xl font-black text-adaptive mb-2">{{ stats.active_licenses }}</p>
                <div class="flex gap-2">
                     <div class="flex items-center gap-1.5 px-3 py-1 bg-indigo-50 dark:bg-indigo-900/20 rounded-full">
                        <span class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 tracking-tight">Prob: {{ stats.trial_licenses }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1 bg-purple-50 dark:bg-purple-900/20 rounded-full">
                        <span class="text-[9px] font-bold text-purple-600 dark:text-purple-400 tracking-tight">Sub: {{ stats.subscription_licenses }}</span>
                    </div>
                </div>
               
            </div>

            <div class="bg-bg-dark/50 backdrop-blur-md p-8 rounded-[32px] shadow-soft-md border border-white/5 flex flex-col items-center text-center">
                <span class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-4">Pending Orders</span>
                <p class="text-4xl font-black text-amber-500 mb-4">{{ stats.pending_orders }}</p>
                <div class="flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-900/20 rounded-full">
                    <span class="text-[10px] font-black text-amber-600 dark:text-amber-400 tracking-tighter uppercase whitespace-nowrap">Requires Action</span>
                </div>
            </div>

            <div class="bg-bg-dark/50 backdrop-blur-md p-8 rounded-[32px] shadow-soft-md border border-white/5 flex flex-col items-center text-center">
                <span class="text-[10px] font-black text-text-muted uppercase tracking-[0.2em] mb-4">Currently Running</span>
                <p class="text-4xl font-black text-brand-teal mb-4">{{ stats.running_projects }}</p>
                <div class="flex items-center gap-1.5 px-3 py-1 bg-brand-teal/10 rounded-full">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-teal opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-teal"></span>
                    </span>
                    <span class="text-[10px] font-black text-brand-teal tracking-tighter uppercase whitespace-nowrap">Live Connections</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Analytics Visualization Card -->
            <div class="bg-bg-dark/50 backdrop-blur-md p-10 rounded-[40px] shadow-soft-md border border-white/5">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-adaptive tracking-tight">Revenue Scale (30 Days)</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-teal text-glow-teal"></span>
                        <span class="text-[10px] font-bold text-text-muted uppercase tracking-widest">Calculated Trajectory</span>
                    </div>
                </div>
                <!-- Dynamic Area Chart -->
                <div class="h-72 w-full bg-white/5 rounded-[32px] relative overflow-hidden group">
                    <svg class="absolute bottom-0 left-0 w-full h-full" preserveAspectRatio="none" viewBox="0 0 100 100">
                        <path :d="revenueChartPath" fill="url(#brand-grad-analytics)" class="transition-all duration-1000 ease-in-out" />
                        <defs>
                            <linearGradient id="brand-grad-analytics" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#4F46E5" stop-opacity="0.6" />
                                <stop offset="100%" stop-color="#4F46E5" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <!-- Overlay Grid Lines (Optional) -->
                    <div class="absolute inset-0 pointer-events-none border-b border-white/5"></div>
                </div>
            </div>

            <!-- Audit Activity Card -->
            <div class="bg-bg-dark/50 backdrop-blur-md p-10 rounded-[40px] shadow-soft-md border border-white/5">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-lg font-black text-adaptive tracking-tight">Security Intelligence</h3>
                    <!-- <button class="text-xs font-black text-brand-teal uppercase tracking-widest hover:underline decoration-2">Deep Dive</button> -->
                </div>
                <div class="space-y-3">
                    <div v-for="activity in recent_activities.data" :key="activity.id" class="flex items-center justify-between p-4 bg-white/5 hover:bg-white/10 rounded-2xl transition-all border border-transparent hover:border-white/10 group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition-colors"
                                :class="{
                                    'bg-emerald-500/10 text-emerald-500': activity.status === 'success',
                                    'bg-red-500/10 text-red-500': activity.status !== 'success'
                                }">
                                <svg v-if="activity.status === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-black text-adaptive leading-tight">{{ activity.product_name }}</p>
                                <p class="text-[11px] text-text-muted font-medium">Domain: {{ activity.domain }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                             <span class="text-[9px] font-black uppercase tracking-tighter block mb-0.5"
                                :class="activity.status === 'success' ? 'text-emerald-500' : 'text-red-500'">
                                {{ activity.status }}
                            </span>
                            <span class="text-[9px] text-text-muted">{{ activity.created_at }}</span>
                        </div>
                    </div>
                    <div v-if="recent_activities.data.length === 0" class="text-center py-8 text-text-muted text-sm scale-95 opacity-50">
                        No recent activity detected.
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex items-center justify-between" v-if="recent_activities.links.length > 3">
                    <div class="flex gap-2">
                        <Link 
                            v-if="recent_activities.prev_page_url" 
                            :href="recent_activities.prev_page_url" 
                            preserve-scroll
                            class="px-4 py-2 bg-white/5 rounded-xl text-[10px] font-bold text-text-muted hover:bg-white/10 transition-colors uppercase tracking-widest"
                        >
                            Previous
                        </Link>
                         <button 
                            v-else 
                            disabled
                            class="px-4 py-2 bg-white/5 rounded-xl text-[10px] font-bold text-text-muted/50 cursor-not-allowed uppercase tracking-widest"
                        >
                            Previous
                        </button>

                        <Link 
                            v-if="recent_activities.next_page_url" 
                            :href="recent_activities.next_page_url" 
                            preserve-scroll
                            class="px-4 py-2 bg-white/5 rounded-xl text-[10px] font-bold text-text-muted hover:bg-white/10 transition-colors uppercase tracking-widest"
                        >
                            Next
                        </Link>
                         <button 
                            v-else 
                            disabled
                            class="px-4 py-2 bg-white/5 rounded-xl text-[10px] font-bold text-text-muted/50 cursor-not-allowed uppercase tracking-widest"
                        >
                            Next
                        </button>
                    </div>
                    <span class="text-[10px] text-text-muted font-medium">
                        Page {{ recent_activities.current_page }} of {{ recent_activities.last_page }}
                    </span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
