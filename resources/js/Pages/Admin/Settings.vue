<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

const props = defineProps({
    currentSettings: {
        type: Object,
        default: () => ({}),
    },
});

const saving = ref(false);

const form = reactive({
    gateway_stripe_active: props.currentSettings?.gateway_stripe_active === '1',
    gateway_stripe_key: props.currentSettings?.gateway_stripe_key || '',
    gateway_stripe_secret: props.currentSettings?.gateway_stripe_secret || '',

    gateway_bkash_active: props.currentSettings?.gateway_bkash_active === '1',
    gateway_bkash_sandbox: props.currentSettings?.gateway_bkash_sandbox === '1' || !props.currentSettings?.gateway_bkash_sandbox,
    gateway_bkash_username: props.currentSettings?.gateway_bkash_username || '',
    gateway_bkash_password: props.currentSettings?.gateway_bkash_password || '',
    gateway_bkash_app_key: props.currentSettings?.gateway_bkash_app_key || '',
    gateway_bkash_app_secret: props.currentSettings?.gateway_bkash_app_secret || '',

    gateway_nagad_active: props.currentSettings?.gateway_nagad_active === '1',
    gateway_nagad_merchant_id: props.currentSettings?.gateway_nagad_merchant_id || '',

    gateway_rocket_active: props.currentSettings?.gateway_rocket_active === '1',
    gateway_rocket_merchant_id: props.currentSettings?.gateway_rocket_merchant_id || '',

    min_client_version: props.currentSettings?.min_client_version || '1.2.0',
    default_theme: props.currentSettings?.default_theme || 'dark-modern',
    base_currency: props.currentSettings?.base_currency || 'USD — United States Dollar',
    exchange_rate: props.currentSettings?.exchange_rate || '114.50',
});

const saveSettings = () => {
    saving.value = true;

    const payload = Object.fromEntries(
        Object.entries(form).map(([key, value]) => [
            key,
            typeof value === 'boolean' ? (value ? '1' : '0') : value,
        ])
    );

    router.post(route('admin.settings.save'), payload, {
        preserveScroll: true,
        onSuccess: () => {
            saving.value = false;
        },
        onError: () => {
            saving.value = false;
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};
</script>

<template>
    <Head title="System Settings" />

    <AuthenticatedLayout>
        <div class="mb-12">
            <Badge status="success" class="!rounded-full px-3 py-1.5">System architecture</Badge>
            <h2 class="mt-4 text-4xl font-black tracking-tight text-text-primary">System architecture</h2>
            <p class="mt-2 text-sm text-text-muted">Configure core protocols, economic units, and payment gateways.</p>
        </div>

        <div class="max-w-6xl space-y-12">
            <div class="space-y-6">
                <h3 class="mb-4 text-sm font-black uppercase tracking-[0.3em] text-text-muted">Payment infrastructure</h3>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <Card class="p-8">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo/10 text-indigo">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M13.911 8.012c0-.528.398-.923 1.072-.923.868 0 1.25.398 1.272.939h1.166c-.024-.954-.741-1.742-2.396-1.742-1.583 0-2.28 1.01-2.28 1.765 0 1.583 2.158 1.34 2.158 2.222 0 .584-.528.922-1.189.922-.954 0-1.424-.516-1.448-1.079H11.1c.023 1.055.844 1.883 2.654 1.883 1.63 0 2.373-.89 2.373-1.847 0-1.711-2.216-1.425-2.216-2.14zm-5.26 1.815V8.192c-.394-.15-.86-.234-1.218-.234-1.385 0-2.32 1.079-2.32 2.673 0 1.594.935 2.673 2.32 2.673.358 0 .824-.084 1.218-.234v-1.636h-.431c-.347.8-.822 1.085-1.139 1.085-.506 0-.825-.45-.825-1.116 0-.666.319-1.116.825-1.116.317 0 .792.285 1.139 1.085h.431zm-3.65-4.526a.936.936 0 011.872 0 .936.936 0 01-1.872 0zM19.1 11.63c0-.666.319-1.116.825-1.116.317 0 .792.285 1.139 1.085h.431V9.827c-.394-.15-.86-.234-1.218-.234-1.385 0-2.32 1.079-2.32 2.673 0 1.594.935 2.673 2.32 2.673.358 0 .824-.084 1.218-.234v-1.636h-.431c-.347.8-.822 1.085-1.139 1.085-.506 0-.825-.45-.825-1.116z" /></svg>
                                </div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">Stripe</h4>
                            </div>
                            <button @click="form.gateway_stripe_active = !form.gateway_stripe_active" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="form.gateway_stripe_active ? 'bg-indigo' : 'bg-panel-line'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.gateway_stripe_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Publishable key</label>
                                <input v-model="form.gateway_stripe_key" type="password" placeholder="pk_live_..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Secret key</label>
                                <input v-model="form.gateway_stripe_secret" type="password" placeholder="sk_live_..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                        </div>
                    </Card>

                    <Card class="p-8">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-pink/10 text-pink font-black text-xs">bkash</div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">bKash</h4>
                            </div>
                            <button @click="form.gateway_bkash_active = !form.gateway_bkash_active" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="form.gateway_bkash_active ? 'bg-pink' : 'bg-panel-line'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.gateway_bkash_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Environment</label>
                                <button @click="form.gateway_bkash_sandbox = !form.gateway_bkash_sandbox" class="rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all" :class="form.gateway_bkash_sandbox ? 'bg-pink text-white' : 'bg-panel-2 text-text-muted border border-panel-line'">
                                    {{ form.gateway_bkash_sandbox ? 'Sandbox' : 'Production' }}
                                </button>
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Merchant username</label>
                                <input v-model="form.gateway_bkash_username" type="text" placeholder="sandboxTokenizedUser02" class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Merchant password</label>
                                <input v-model="form.gateway_bkash_password" type="password" placeholder="Merchant password..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">App key</label>
                                <input v-model="form.gateway_bkash_app_key" type="password" placeholder="Enter key..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">App secret</label>
                                <input v-model="form.gateway_bkash_app_secret" type="password" placeholder="Enter secret..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                        </div>
                    </Card>

                    <Card class="p-8">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-danger/8 text-danger font-black text-[10px]">NAGAD</div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">Nagad</h4>
                            </div>
                            <button @click="form.gateway_nagad_active = !form.gateway_nagad_active" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="form.gateway_nagad_active ? 'bg-danger' : 'bg-panel-line'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.gateway_nagad_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Merchant ID</label>
                                <input v-model="form.gateway_nagad_merchant_id" type="text" placeholder="Enter ID..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                        </div>
                    </Card>

                    <Card class="p-8">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple/10 text-purple font-black text-[10px]">ROCKET</div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">Rocket</h4>
                            </div>
                            <button @click="form.gateway_rocket_active = !form.gateway_rocket_active" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="form.gateway_rocket_active ? 'bg-purple' : 'bg-panel-line'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.gateway_rocket_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Merchant ID</label>
                                <input v-model="form.gateway_rocket_merchant_id" type="text" placeholder="Enter ID..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                        </div>
                    </Card>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
                <Card class="p-10">
                    <div class="mb-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>
                        </div>
                        <h3 class="text-xl font-black tracking-tight text-text-primary">API core</h3>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Minimum client version</label>
                            <input v-model="form.min_client_version" type="text" class="w-full rounded-2xl border border-panel-line bg-panel-2 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Global theme default</label>
                            <select v-model="form.default_theme" class="w-full appearance-none rounded-2xl border border-panel-line bg-panel-2 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                                <option value="dark-modern">Dark Modern (Cyberpunk)</option>
                                <option value="light-modern">Light Modern (Clean)</option>
                                <option value="solarized-dark">Solarized Dark (Code)</option>
                                <option value="tokyo-night">Tokyo Night (Neon)</option>
                            </select>
                        </div>
                    </div>
                </Card>

                <Card class="p-10">
                    <div class="mb-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo/10 text-indigo">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zM17 13a3 3 0 10-6 0 3 3 0 006 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-black tracking-tight text-text-primary">Economic registry</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Asset currency</label>
                            <select v-model="form.base_currency" class="w-full appearance-none rounded-2xl border border-panel-line bg-panel-2 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                                <option>USD — United States Dollar</option>
                                <option>BDT — Bangladeshi Taka</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Current delta (USD/BDT)</label>
                            <input v-model="form.exchange_rate" type="number" step="0.01" class="w-full rounded-2xl border border-panel-line bg-panel-2 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                        </div>
                    </div>
                </Card>
            </div>

            <div class="flex justify-end pt-4">
                <Button type="button" variant="primary" :disabled="saving" @click="saveSettings">
                    {{ saving ? 'Syncing...' : 'Commit changes' }}
                </Button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
