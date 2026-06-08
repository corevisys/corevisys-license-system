<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    currentSettings: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    gateway_stripe_active: props.currentSettings?.gateway_stripe_active === '1',
    gateway_stripe_key: props.currentSettings?.gateway_stripe_key || '',
    gateway_stripe_secret: props.currentSettings?.gateway_stripe_secret || '',
    
    gateway_bkash_active: props.currentSettings?.gateway_bkash_active === '1',
    gateway_bkash_app_key: props.currentSettings?.gateway_bkash_app_key || '',
    gateway_bkash_app_secret: props.currentSettings?.gateway_bkash_app_secret || '',
    
    gateway_nagad_active: props.currentSettings?.gateway_nagad_active === '1',
    gateway_nagad_merchant_id: props.currentSettings?.gateway_nagad_merchant_id || '',
    
    gateway_rocket_active: props.currentSettings?.gateway_rocket_active === '1',
    gateway_rocket_merchant_id: props.currentSettings?.gateway_rocket_merchant_id || '',

    min_client_version: props.currentSettings?.min_client_version || '1.2.0',
    default_theme: props.currentSettings?.default_theme || 'dark-modern',
    base_currency: props.currentSettings?.base_currency || 'USD — United States Dollar',
    exchange_rate: props.currentSettings?.exchange_rate || '114.50'
});

const saveSettings = () => {
    // Transform booleans to 1/0 for database
    const payload = { ...form.data() };
    Object.keys(payload).forEach(key => {
        if (typeof payload[key] === 'boolean') {
            payload[key] = payload[key] ? '1' : '0';
        }
    });

    form.transform((data) => ({
        ...payload
    })).post(route('admin.settings.save'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success notification handled by Inertia flash or simple message
        }
    });
};
</script>

<template>
    <Head title="System Settings" />

    <AuthenticatedLayout>
        <!-- Page Header -->
        <div class="mb-12">
            <h2 class="text-4xl font-black text-adaptive tracking-tight mb-2">
                System <span class="text-brand-teal text-glow-teal">Architecture</span>
            </h2>
            <p class="text-text-muted font-medium italic">Configure core protocols, economic units, and payment gateways.</p>
        </div>

        <div class="max-w-6xl space-y-12">
            <!-- Payment Gateways Section -->
            <div class="space-y-6">
                <h3 class="text-sm font-black text-text-muted uppercase tracking-[0.3em] mb-4">Payment Infrastructure</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Stripe -->
                    <div class="glass p-8 rounded-[40px] border-b-4 border-transparent hover:border-indigo-500/30 transition-all group">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-[#635bff]/10 rounded-2xl flex items-center justify-center text-[#635bff]">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M13.911 8.012c0-.528.398-.923 1.072-.923.868 0 1.25.398 1.272.939h1.166c-.024-.954-.741-1.742-2.396-1.742-1.583 0-2.28 1.01-2.28 1.765 0 1.583 2.158 1.34 2.158 2.222 0 .584-.528.922-1.189.922-.954 0-1.424-.516-1.448-1.079H11.1c.023 1.055.844 1.883 2.654 1.883 1.63 0 2.373-.89 2.373-1.847 0-1.711-2.216-1.425-2.216-2.14zm-5.26 1.815V8.192c-.394-.15-.86-.234-1.218-.234-1.385 0-2.32 1.079-2.32 2.673 0 1.594.935 2.673 2.32 2.673.358 0 .824-.084 1.218-.234v-1.636h-.431c-.347.8-.822 1.085-1.139 1.085-.506 0-.825-.45-.825-1.116 0-.666.319-1.116.825-1.116.317 0 .792.285 1.139 1.085h.431zm-3.65-4.526a.936.936 0 011.872 0 .936.936 0 01-1.872 0zM19.1 11.63c0-.666.319-1.116.825-1.116.317 0 .792.285 1.139 1.085h.431V9.827c-.394-.15-.86-.234-1.218-.234-1.385 0-2.32 1.079-2.32 2.673 0 1.594.935 2.673 2.32 2.673.358 0 .824-.084 1.218-.234v-1.636h-.431c-.347.8-.822 1.085-1.139 1.085-.506 0-.825-.45-.825-1.116z" /></svg>
                                </div>
                                <h4 class="font-black text-adaptive uppercase tracking-tight">Stripe</h4>
                            </div>
                            <button @click="form.gateway_stripe_active = !form.gateway_stripe_active" 
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                    :class="form.gateway_stripe_active ? 'bg-indigo-600' : 'bg-white/10'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                      :class="form.gateway_stripe_active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-black text-text-muted uppercase tracking-widest mb-2">Publishable Key</label>
                                <input v-model="form.gateway_stripe_key" type="password" placeholder="pk_live_..." class="w-full glass border-none rounded-xl p-3 text-xs font-bold text-adaptive focus:ring-1 focus:ring-indigo-500/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black text-text-muted uppercase tracking-widest mb-2">Secret Key</label>
                                <input v-model="form.gateway_stripe_secret" type="password" placeholder="sk_live_..." class="w-full glass border-none rounded-xl p-3 text-xs font-bold text-adaptive focus:ring-1 focus:ring-indigo-500/50 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- bKash -->
                    <div class="glass p-8 rounded-[40px] border-b-4 border-transparent hover:border-pink-500/30 transition-all group">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-pink-500/10 rounded-2xl flex items-center justify-center text-pink-500 font-black text-xs">
                                    bkash
                                </div>
                                <h4 class="font-black text-adaptive uppercase tracking-tight">bKash</h4>
                            </div>
                            <button @click="form.gateway_bkash_active = !form.gateway_bkash_active" 
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                    :class="form.gateway_bkash_active ? 'bg-pink-600' : 'bg-white/10'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                      :class="form.gateway_bkash_active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-black text-text-muted uppercase tracking-widest mb-2">App Key</label>
                                <input v-model="form.gateway_bkash_app_key" type="password" placeholder="Enter key..." class="w-full glass border-none rounded-xl p-3 text-xs font-bold text-adaptive focus:ring-1 focus:ring-pink-500/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black text-text-muted uppercase tracking-widest mb-2">App Secret</label>
                                <input v-model="form.gateway_bkash_app_secret" type="password" placeholder="Enter secret..." class="w-full glass border-none rounded-xl p-3 text-xs font-bold text-adaptive focus:ring-1 focus:ring-pink-500/50 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Nagad -->
                    <div class="glass p-8 rounded-[40px] border-b-4 border-transparent hover:border-rose-500/30 transition-all group">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-rose-500/10 rounded-2xl flex items-center justify-center text-rose-500 font-black text-[10px]">
                                    NAGAD
                                </div>
                                <h4 class="font-black text-adaptive uppercase tracking-tight">Nagad</h4>
                            </div>
                            <button @click="form.gateway_nagad_active = !form.gateway_nagad_active" 
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                    :class="form.gateway_nagad_active ? 'bg-rose-600' : 'bg-white/10'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                      :class="form.gateway_nagad_active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-black text-text-muted uppercase tracking-widest mb-2">Merchant ID</label>
                                <input v-model="form.gateway_nagad_merchant_id" type="text" placeholder="Enter ID..." class="w-full glass border-none rounded-xl p-3 text-xs font-bold text-adaptive focus:ring-1 focus:ring-rose-500/50 transition-all">
                            </div>
                        </div>
                    </div>

                    <!-- Rocket -->
                    <div class="glass p-8 rounded-[40px] border-b-4 border-transparent hover:border-purple-500/30 transition-all group">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-purple-500/10 rounded-2xl flex items-center justify-center text-purple-500 font-black text-[10px]">
                                    ROCKET
                                </div>
                                <h4 class="font-black text-adaptive uppercase tracking-tight">Rocket</h4>
                            </div>
                            <button @click="form.gateway_rocket_active = !form.gateway_rocket_active" 
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none"
                                    :class="form.gateway_rocket_active ? 'bg-purple-600' : 'bg-white/10'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                      :class="form.gateway_rocket_active ? 'translate-x-6' : 'translate-x-1'"></span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[9px] font-black text-text-muted uppercase tracking-widest mb-2">Merchant ID</label>
                                <input v-model="form.gateway_rocket_merchant_id" type="text" placeholder="Enter ID..." class="w-full glass border-none rounded-xl p-3 text-xs font-bold text-adaptive focus:ring-1 focus:ring-purple-500/50 transition-all">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Existing API & Economic Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- API Infrastructure -->
                <div class="glass p-10 rounded-[40px]">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-12 h-12 bg-brand-teal/10 rounded-2xl flex items-center justify-center text-brand-teal">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                        </div>
                        <h3 class="font-black text-xl text-adaptive tracking-tight">API Core</h3>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em]">Minimum Client Version</label>
                            <input v-model="form.min_client_version" type="text" class="w-full glass border-none rounded-2xl p-4 text-sm font-bold text-adaptive focus:ring-1 focus:ring-brand-teal/50 transition-all">
                        </div>
                        <div class="space-y-2 transform hover:scale-[1.02] transition-transform">
                            <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em]">Global Theme Default</label>
                            <select v-model="form.default_theme" class="w-full glass border-none rounded-2xl p-4 text-sm font-bold text-adaptive focus:ring-1 focus:ring-brand-teal/50 appearance-none transition-all cursor-pointer">
                                <option value="dark-modern">Dark Modern (Cyberpunk)</option>
                                <option value="light-modern">Light Modern (Clean)</option>
                                <option value="solarized-dark">Solarized Dark (Code)</option>
                                <option value="tokyo-night">Tokyo Night (Neon)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Economic Configuration -->
                <div class="glass p-10 rounded-[40px]">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-12 h-12 bg-brand-blue/10 rounded-2xl flex items-center justify-center text-brand-blue">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 13a3 3 0 10-6 0 3 3 0 006 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="font-black text-xl text-adaptive tracking-tight">Economic Registry</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em]">Asset Currency</label>
                            <select v-model="form.base_currency" class="w-full glass border-none rounded-2xl p-4 text-sm font-bold text-adaptive focus:ring-1 focus:ring-brand-blue/50 appearance-none transition-all">
                                <option>USD — United States Dollar</option>
                                <option>BDT — Bangladeshi Taka</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-text-muted uppercase tracking-[0.2em]">Current Delta (USD/BDT)</label>
                            <input v-model="form.exchange_rate" type="number" step="0.01" class="w-full glass border-none rounded-2xl p-4 text-sm font-bold text-adaptive focus:ring-1 focus:ring-brand-blue/50 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-6 pt-4">
                <button @click="saveSettings" 
                        :disabled="form.processing"
                        class="group relative px-12 py-5 bg-gradient-to-r from-brand-teal to-brand-blue text-slate-900 rounded-[24px] text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-brand-teal/20 hover:scale-105 active:scale-95 transition-all disabled:opacity-50 overflow-hidden">
                    <div class="absolute inset-0 bg-white/20 -translate-x-full group-hover:translate-x-full transition-transform duration-700 skew-x-12"></div>
                    <span class="relative z-10">{{ form.processing ? 'Syncing...' : 'Commit Changes' }}</span>
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.glass {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.05);
}
</style>
