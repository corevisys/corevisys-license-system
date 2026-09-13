<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            active_licenses: 0,
            total_revenue: 0,
            avg_uptime: 99.99,
            server_load: 0
        })
    },
    charts: {
        type: Object,
        default: () => ({
            distribution: {},
            orders_trend: [0, 0, 0, 0, 0, 0],
            months: ['J', 'F', 'M', 'A', 'M', 'J'],
            status_summary: {}
        })
    }
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
    const stepTime = Math.abs(Math.floor(duration / (range || 1)));
    
    const timer = setInterval(() => {
        current += (range / (duration / 20)); // Smoother increment
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
        <!-- Header -->
        <div class="mb-12">
            <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">
                Operational <span class="text-brand-teal text-glow-teal">Analytics</span>
            </h2>
            <p class="text-text-muted font-medium">Deep insights into your license fleet and system health.</p>
        </div>

        <!-- Main Analytics Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Spending Trend Chart -->
            <div class="glass p-8 rounded-[40px] relative overflow-hidden group">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-1">Expenditure Trend</p>
                        <h4 class="text-2xl font-black text-adaptive">${{ displayRevenue.toLocaleString() }} <span class="text-[10px] text-text-muted font-bold tracking-normal italic ml-2">Total</span></h4>
                    </div>
                    <div class="w-12 h-12 bg-brand-teal/10 rounded-2xl flex items-center justify-center text-brand-teal">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                </div>
                
                <!-- Real Data Chart -->
                <div class="h-48 flex items-end gap-2 px-2">
                    <div v-for="(val, index) in charts.orders_trend" :key="index" 
                         class="flex-1 bg-gradient-to-t from-brand-teal/20 to-brand-teal rounded-t-lg transition-all duration-1000 relative group/bar"
                         :style="{ 
                            height: `${Math.max((val / Math.max(...charts.orders_trend, 1)) * 100, 5)}%`,
                            opacity: 0.4 + (index * 0.1) 
                         }">
                         <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-slate-900 border border-white/10 px-2 py-1 rounded text-[10px] font-black pointer-events-none opacity-0 group-hover/bar:opacity-100 transition-opacity">
                            ${{ val }}
                         </div>
                    </div>
                </div>
                
                <div class="mt-6 flex justify-between text-[10px] font-black text-text-muted uppercase tracking-widest px-2">
                    <span v-for="month in charts.months" :key="month">{{ month }}</span>
                </div>
            </div>

            <!-- License Distribution / Status -->
            <div class="glass p-8 rounded-[40px] group">
                <div class="flex items-center justify-between mb-10">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-text-muted mb-1">License Fleet</p>
                        <h4 class="text-2xl font-black text-adaptive">{{ stats.active_licenses }} Active Nodes</h4>
                    </div>
                    <div class="px-4 py-1.5 glass rounded-full text-[10px] font-black text-brand-blue uppercase tracking-widest animate-pulse">
                        Live Analytics
                    </div>
                </div>

                <div class="space-y-8">
                    <div v-if="Object.keys(charts.distribution).length > 0">
                        <div v-for="(count, product) in charts.distribution" :key="product" class="mb-4 last:mb-0">
                            <div class="flex justify-between mb-2 text-xs font-bold uppercase tracking-wider">
                                <span class="text-adaptive">{{ product }}</span>
                                <span class="text-brand-teal">{{ count }} Units</span>
                            </div>
                            <div class="h-2 glass rounded-full overflow-hidden">
                                <div class="h-full bg-brand-teal shadow-glow-teal transition-all duration-1000"
                                     :style="{ width: `${(count / Object.values(charts.distribution).reduce((a,b) => a+b, 0)) * 100}%` }"></div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-10 italic text-text-muted">
                        No license data found.
                    </div>

                    <div class="grid grid-cols-2 gap-4 border-t border-white/5 pt-8">
                        <div class="p-5 glass rounded-3xl text-center">
                            <p class="text-xs font-bold text-text-muted mb-2 uppercase">Uptime</p>
                            <p class="text-2xl font-black text-adaptive">{{ stats.avg_uptime }}%</p>
                        </div>
                        <div class="p-5 glass rounded-3xl text-center">
                            <p class="text-xs font-bold text-text-muted mb-2 uppercase">System Load</p>
                            <p class="text-2xl font-black text-adaptive" :class="stats.server_load > 80 ? 'text-rose-400' : 'text-brand-blue'">{{ stats.server_load }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass p-6 rounded-3xl flex items-center gap-6 border-transparent hover:border-white/10 transition-colors">
                <div class="w-14 h-14 bg-white/5 rounded-2xl flex items-center justify-center text-amber-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Expiring Soon</h5>
                    <p class="text-xl font-black text-adaptive">{{ charts.status_summary.expiring || 0 }}</p>
                </div>
            </div>
            <div class="glass p-6 rounded-3xl flex items-center gap-6 border-transparent hover:border-white/10 transition-colors">
                <div class="w-14 h-14 bg-white/5 rounded-2xl flex items-center justify-center text-rose-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Expired/Revoked</h5>
                    <p class="text-xl font-black text-adaptive">{{ (charts.status_summary.expired || 0) + (charts.status_summary.revoked || 0) }}</p>
                </div>
            </div>
            <div class="glass p-6 rounded-3xl flex items-center gap-6 border-transparent hover:border-white/10 transition-colors">
                <div class="w-14 h-14 bg-white/5 rounded-2xl flex items-center justify-center text-brand-teal">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <div>
                    <h5 class="text-xs font-black text-text-muted uppercase tracking-widest mb-1">Total Assets</h5>
                    <p class="text-xl font-black text-adaptive">{{ Object.values(charts.status_summary).reduce((a,b) => a+b, 0) }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
