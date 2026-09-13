<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    license: Object,
    api_base_url: String
});

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
};
</script>

<template>
    <Head :title="'Configure - ' + license.product_name" />

    <AuthenticatedLayout>
        <div class="max-w-5xl mx-auto py-12">
            <!-- Header -->
            <div class="mb-12 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-4 mb-3">
                        <Link :href="route('licenses')" class="p-2 hover:bg-white/5 rounded-xl text-text-muted transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        </Link>
                        <h2 class="text-3xl font-black text-adaptive tracking-tight">License <span class="text-brand-teal">Configuration</span></h2>
                    </div>
                    <p class="text-text-muted font-medium italic">Integration guide and security settings for {{ license.product_name }}.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span :class="[
                        'px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border',
                        license.status === 'active' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20 shadow-glow-emerald' : 'bg-rose-500/10 text-rose-500 border-rose-500/20'
                    ]">
                        {{ license.status }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content (Instructions) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Credentials Card -->
                    <div class="glass p-8 rounded-[32px] border border-white/10 shadow-2xl">
                        <h3 class="text-lg font-black text-adaptive mb-6 flex items-center gap-3">
                            <svg class="w-5 h-5 text-brand-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                            Access Credentials
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="text-[10px] font-black text-text-muted uppercase tracking-widest mb-2 block">Your License Key</label>
                                <div class="flex items-center gap-3">
                                    <code class="flex-1 bg-bg-dark/50 px-4 py-3 rounded-2xl text-brand-teal font-mono text-sm border border-white/5 break-all">
                                        {{ license.license_key }}
                                    </code>
                                    <button @click="copyToClipboard(license.license_key)" class="p-3 bg-brand-teal/10 text-brand-teal rounded-2xl hover:bg-brand-teal hover:text-slate-900 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Integration Roadmap -->
                    <div class="bg-bg-dark/50 backdrop-blur-md p-8 rounded-[32px] border border-white/5 shadow-soft-xl">
                        <h3 class="text-xl font-black text-adaptive mb-8">Integration Guide</h3>
                        
                        <div class="space-y-10">
                            <!-- Step 1 -->
                            <div class="flex gap-6">
                                <div class="w-10 h-10 bg-brand-teal/10 text-brand-teal rounded-2xl flex items-center justify-center font-black text-lg shrink-0 border border-brand-teal/20">1</div>
                                <div>
                                    <h4 class="text-sm font-black text-adaptive uppercase tracking-widest mb-2">Initialize Activation</h4>
                                    <p class="text-text-muted text-sm leading-relaxed mb-4">Send a POST request to our validation endpoint with your license key. This links your current environment (IP/Domain) to our system.</p>
                                    <div class="bg-slate-900 rounded-2xl p-4 border border-white/5 overflow-x-auto text-xs font-mono text-slate-300">
                                        <div class="text-brand-blue mb-1">Method: <span class="text-brand-teal">POST</span></div>
                                        <div class="text-brand-blue">URL: <span class="text-slate-400 font-bold">{{ props.api_base_url }}/license/activate</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            <div class="flex gap-6">
                                <div class="w-10 h-10 bg-brand-teal/10 text-brand-teal rounded-2xl flex items-center justify-center font-black text-lg shrink-0 border border-brand-teal/20">2</div>
                                <div>
                                    <h4 class="text-sm font-black text-adaptive uppercase tracking-widest mb-2">Payload Requirements</h4>
                                    <p class="text-text-muted text-sm leading-relaxed mb-4">Include the following JSON payload in your request header:</p>
                                    <div class="bg-slate-900 rounded-2xl p-4 border border-white/5 text-xs font-mono">
<pre class="text-emerald-400">
{
  "license_key": "{{ license.license_key }}",
  "domain": "yourdomain.com",
  "ip_address": "8.8.8.8"
}
</pre>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3 -->
                            <div class="flex gap-6">
                                <div class="w-10 h-10 bg-brand-teal/10 text-brand-teal rounded-2xl flex items-center justify-center font-black text-lg shrink-0 border border-brand-teal/20">3</div>
                                <div>
                                    <h4 class="text-sm font-black text-adaptive uppercase tracking-widest mb-2">Validation Loop</h4>
                                    <p class="text-text-muted text-sm leading-relaxed">Our API will return a digitally signed token. Store this token locally to check the license status periodically without making external calls every time.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Settings) -->
                <div class="space-y-8">
                    <!-- Binding Status -->
                    <div class="glass p-8 rounded-[32px] border border-white/10 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-brand-teal/5 rounded-full blur-2xl group-hover:bg-brand-teal/10 transition-all"></div>
                        <h3 class="text-sm font-black text-white mb-6 uppercase tracking-widest flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-brand-teal animate-pulse"></div>
                             Environment Binding
                        </h3>
                        
                        <div class="space-y-6">
                            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                                <label class="text-[9px] font-black text-text-muted uppercase tracking-[0.2em] mb-1 block">Bound Domain</label>
                                <p class="text-sm font-mono text-adaptive">{{ license.bound_domain || 'Not Bound' }}</p>
                            </div>
                            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                                <label class="text-[9px] font-black text-text-muted uppercase tracking-[0.2em] mb-1 block">Bound IP Address</label>
                                <p class="text-sm font-mono text-adaptive">{{ license.bound_ip || 'Not Bound' }}</p>
                            </div>
                            
                            <div class="p-4 bg-white/5 rounded-2xl border border-white/5">
                                <label class="text-[9px] font-black text-text-muted uppercase tracking-[0.2em] mb-1 block">Activation Limit</label>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-black text-adaptive">{{ license.current_usage }} / {{ license.activation_limit }}</p>
                                    <div class="flex-1 ml-4 h-1 bg-white/5 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-teal transition-all duration-1000" :style="{ width: (license.activation_limit > 0 ? (license.current_usage / license.activation_limit * 100) : 0) + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-4">
                                <p class="text-[11px] text-text-muted leading-relaxed italic mb-4">Your license is bound to the following environment. Contact our support team if you need to transfer the license to a different domain.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Support Card -->
                    <div class="p-8 rounded-[32px] bg-gradient-to-br from-brand-teal to-brand-blue shadow-lg shadow-brand-teal/20">
                        <h3 class="font-black text-slate-900 text-lg mb-2 leading-tight">Need help with Integration?</h3>
                        <p class="text-slate-900/70 text-xs font-bold mb-6">Our technical team is available 24/7 to help you deploy.</p>
                        <button class="w-full py-3 bg-white/20 backdrop-blur-md rounded-xl text-slate-900 text-[10px] font-black uppercase tracking-widest hover:bg-white/30 transition-all">
                            Contact Support
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass {
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(20px);
}
</style>
