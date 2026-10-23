<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import Alert from '@/Components/UI/Alert.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, reactive, ref } from 'vue';

const props = defineProps({
    currentSettings: {
        type: Object,
        default: () => ({}),
    },
    signing: {
        type: Object,
        default: () => ({}),
    },
    runtime: {
        type: Object,
        default: () => ({}),
    },
    protocol: {
        type: Object,
        default: () => ({}),
    },
});

const saving = ref(false);

// A masked secret is never sent back on save — the backend also guards against
// this, but we omit them here so real credentials are never clobbered.
const isMasked = (value) => typeof value === 'string' && (value === '******' || value.startsWith('***'));

const form = reactive({
    gateway_stripe_active: props.currentSettings?.gateway_stripe_active === '1',
    gateway_stripe_key: isMasked(props.currentSettings?.gateway_stripe_key) ? '' : (props.currentSettings?.gateway_stripe_key || ''),
    gateway_stripe_secret: isMasked(props.currentSettings?.gateway_stripe_secret) ? '' : (props.currentSettings?.gateway_stripe_secret || ''),

    gateway_bkash_active: props.currentSettings?.gateway_bkash_active === '1',
    gateway_bkash_sandbox: props.currentSettings?.gateway_bkash_sandbox === '1' || !props.currentSettings?.gateway_bkash_sandbox,
    gateway_bkash_username: props.currentSettings?.gateway_bkash_username || '',
    gateway_bkash_password: isMasked(props.currentSettings?.gateway_bkash_password) ? '' : (props.currentSettings?.gateway_bkash_password || ''),
    gateway_bkash_app_key: isMasked(props.currentSettings?.gateway_bkash_app_key) ? '' : (props.currentSettings?.gateway_bkash_app_key || ''),
    gateway_bkash_app_secret: isMasked(props.currentSettings?.gateway_bkash_app_secret) ? '' : (props.currentSettings?.gateway_bkash_app_secret || ''),

    api_enabled: (props.currentSettings?.api_enabled ?? 'true') !== 'false' && (props.currentSettings?.api_enabled ?? 'true') !== '0',
    min_supported_version: props.currentSettings?.min_supported_version || '1.2.0',
    default_theme: props.currentSettings?.default_theme || 'terminal',
    base_currency: (props.currentSettings?.base_currency || 'USD').split('—')[0].trim(),
    exchange_rate: props.currentSettings?.exchange_rate || '114.50',
});

// Per-field reveal toggles for sensitive inputs.
const revealed = reactive({});
const toggleReveal = (field) => {
    revealed[field] = !revealed[field];
};
const inputType = (field) => (revealed[field] ? 'text' : 'password');

const saveSettings = () => {
    saving.value = true;

    const payload = Object.fromEntries(
        Object.entries(form)
            .filter(([, value]) => !isMasked(value))
            .map(([key, value]) => [
                key,
                typeof value === 'boolean' ? (value ? '1' : '0') : value,
            ])
    );

    router.post(route('admin.settings.save'), payload, {
        preserveScroll: true,
        onFinish: () => {
            saving.value = false;
        },
    });
};

const currencyLabel = (code) => ({ USD: '$', BDT: '৳' }[code] ?? code);

const infraBadges = computed(() => [
    { label: 'Queue', value: props.runtime?.queue_driver || 'database' },
    { label: 'Session', value: props.runtime?.session_driver || 'database' },
    { label: 'Cache', value: props.runtime?.cache_driver || 'database' },
    { label: 'Scheduler', value: props.runtime?.scheduler_command || 'license:renew-subscriptions' },
]);

const publicKeyConfigured = computed(() => props.signing?.public_key_configured === true);
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
            <Alert v-if="$page.props.flash?.error" variant="danger">
                {{ $page.props.flash.error }}
            </Alert>
            <Alert v-else-if="$page.props.flash?.success" variant="success">
                {{ $page.props.flash.success }}
            </Alert>

            <!-- Emergency controls -->
            <Card class="p-8">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl" :class="form.api_enabled ? 'bg-teal/10 text-teal' : 'bg-danger/10 text-danger'">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black tracking-tight text-text-primary">API emergency kill-switch</h3>
                            <p class="mt-1 text-xs text-text-muted">
                                Instantly suspends all <span class="font-mono">/api/v1</span> traffic (maintenance or attack).
                                Currently
                                <span :class="form.api_enabled ? 'font-black text-teal' : 'font-black text-danger'">
                                    {{ form.api_enabled ? 'ONLINE' : 'SUSPENDED' }}
                                </span>.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="form.api_enabled = !form.api_enabled"
                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none"
                        :class="form.api_enabled ? 'bg-teal' : 'bg-danger'"
                        :aria-pressed="form.api_enabled"
                    >
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white transition-transform" :class="form.api_enabled ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </Card>

            <!-- Payment infrastructure -->
            <div class="space-y-6">
                <h3 class="mb-4 text-sm font-black uppercase tracking-[0.3em] text-text-muted">Payment infrastructure</h3>
                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <Card class="p-8">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-provider-stripe/10 text-provider-stripe">
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M13.911 8.012c0-.528.398-.923 1.072-.923.868 0 1.25.398 1.272.939h1.166c-.024-.954-.741-1.742-2.396-1.742-1.583 0-2.28 1.01-2.28 1.765 0 1.583 2.158 1.34 2.158 2.222 0 .584-.528.922-1.189.922-.954 0-1.424-.516-1.448-1.079H11.1c.023 1.055.844 1.883 2.654 1.883 1.63 0 2.373-.89 2.373-1.847 0-1.711-2.216-1.425-2.216-2.14zm-5.26 1.815V8.192c-.394-.15-.86-.234-1.218-.234-1.385 0-2.32 1.079-2.32 2.673 0 1.594.935 2.673 2.32 2.673.358 0 .824-.084 1.218-.234v-1.636h-.431c-.347.8-.822 1.085-1.139 1.085-.506 0-.825-.45-.825-1.116 0-.666.319-1.116.825-1.116.317 0 .792.285 1.139 1.085h.431zm-3.65-4.526a.936.936 0 011.872 0 .936.936 0 01-1.872 0zM19.1 11.63c0-.666.319-1.116.825-1.116.317 0 .792.285 1.139 1.085h.431V9.827c-.394-.15-.86-.234-1.218-.234-1.385 0-2.32 1.079-2.32 2.673 0 1.594.935 2.673 2.32 2.673.358 0 .824-.084 1.218-.234v-1.636h-.431c-.347.8-.822 1.085-1.139 1.085-.506 0-.825-.45-.825-1.116z" /></svg>
                                </div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">Stripe</h4>
                            </div>
                            <button @click="form.gateway_stripe_active = !form.gateway_stripe_active" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="form.gateway_stripe_active ? 'bg-provider-stripe' : 'bg-panel-line'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.gateway_stripe_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Publishable key</label>
                                <div class="relative">
                                    <input v-model="form.gateway_stripe_key" :type="inputType('stripe_key')" placeholder="pk_live_..." class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 pr-11 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                                    <button type="button" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-muted hover:text-text-primary" :aria-label="revealed.stripe_key ? 'Hide' : 'Show'" @click="toggleReveal('stripe_key')">
                                        <svg v-if="revealed.stripe_key" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Secret key</label>
                                <div class="relative">
                                    <input v-model="form.gateway_stripe_secret" :type="inputType('stripe_secret')" placeholder="Leave blank to keep current" class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 pr-11 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                                    <button type="button" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-muted hover:text-text-primary" :aria-label="revealed.stripe_secret ? 'Hide' : 'Show'" @click="toggleReveal('stripe_secret')">
                                        <svg v-if="revealed.stripe_secret" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card class="p-8">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-provider-bkash/10 text-provider-bkash font-black text-xs">bkash</div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">bKash</h4>
                            </div>
                            <button @click="form.gateway_bkash_active = !form.gateway_bkash_active" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" :class="form.gateway_bkash_active ? 'bg-provider-bkash' : 'bg-panel-line'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.gateway_bkash_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Environment</label>
                                <button @click="form.gateway_bkash_sandbox = !form.gateway_bkash_sandbox" class="rounded-xl px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] transition-all" :class="form.gateway_bkash_sandbox ? 'bg-provider-bkash text-text-primary' : 'bg-panel-2 text-text-muted border border-panel-line'">
                                    {{ form.gateway_bkash_sandbox ? 'Sandbox' : 'Production' }}
                                </button>
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Merchant username</label>
                                <input v-model="form.gateway_bkash_username" type="text" placeholder="sandboxTokenizedUser02" class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Merchant password</label>
                                <div class="relative">
                                    <input v-model="form.gateway_bkash_password" :type="inputType('bkash_password')" placeholder="Leave blank to keep current" class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 pr-11 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                                    <button type="button" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-muted hover:text-text-primary" @click="toggleReveal('bkash_password')">
                                        <svg v-if="revealed.bkash_password" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">App key</label>
                                <div class="relative">
                                    <input v-model="form.gateway_bkash_app_key" :type="inputType('bkash_app_key')" placeholder="Leave blank to keep current" class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 pr-11 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                                    <button type="button" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-muted hover:text-text-primary" @click="toggleReveal('bkash_app_key')">
                                        <svg v-if="revealed.bkash_app_key" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">App secret</label>
                                <div class="relative">
                                    <input v-model="form.gateway_bkash_app_secret" :type="inputType('bkash_app_secret')" placeholder="Leave blank to keep current" class="w-full rounded-xl border border-panel-line bg-panel-2 p-3 pr-11 text-xs font-bold text-text-primary focus:border-amber focus:outline-none">
                                    <button type="button" class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-text-muted hover:text-text-primary" @click="toggleReveal('bkash_app_secret')">
                                        <svg v-if="revealed.bkash_app_secret" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                                        <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card class="p-8 opacity-60">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-danger/8 text-danger font-black text-[10px]">NAGAD</div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">Nagad</h4>
                                <span class="rounded-full border border-panel-line bg-panel-2 px-2 py-[2px] text-[9px] font-black uppercase tracking-[0.18em] text-amber">Coming soon</span>
                            </div>
                            <button type="button" disabled aria-disabled="true" class="relative inline-flex h-6 w-11 cursor-not-allowed items-center rounded-full bg-panel-line">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white/60 transition-transform translate-x-1" />
                            </button>
                        </div>
                        <p class="text-xs text-text-muted">The Nagad driver is not yet available. This gateway will be enabled in a future release.</p>
                    </Card>

                    <Card class="p-8 opacity-60">
                        <div class="mb-8 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-provider-rocket/10 text-provider-rocket font-black text-[10px]">ROCKET</div>
                                <h4 class="text-lg font-black uppercase tracking-[0.2em] text-text-primary">Rocket</h4>
                                <span class="rounded-full border border-panel-line bg-panel-2 px-2 py-[2px] text-[9px] font-black uppercase tracking-[0.18em] text-amber">Coming soon</span>
                            </div>
                            <button type="button" disabled aria-disabled="true" class="relative inline-flex h-6 w-11 cursor-not-allowed items-center rounded-full bg-panel-line">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white/60 transition-transform translate-x-1" />
                            </button>
                        </div>
                        <p class="text-xs text-text-muted">The Rocket driver is not yet available. This gateway will be enabled in a future release.</p>
                    </Card>
                </div>
            </div>

            <!-- API core + Economic registry -->
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
                            <input v-model="form.min_supported_version" type="text" class="w-full rounded-2xl border border-panel-line bg-panel-2 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                            <p class="text-[11px] text-text-muted">Enforced by <span class="font-mono">CheckClientVersion</span> against the <span class="font-mono">X-API-Version</span> header.</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Global theme default</label>
                            <select v-model="form.default_theme" class="w-full appearance-none rounded-2xl border border-panel-line bg-panel-2 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                                <option value="dark-modern">Dark Modern (Cyberpunk)</option>
                                <option value="light-modern">Light Modern (Clean)</option>
                                <option value="solarized-dark">Solarized Dark (Code)</option>
                                <option value="tokyo-night">Tokyo Night (Neon)</option>
                                <option value="terminal">Terminal</option>
                            </select>
                        </div>
                    </div>
                </Card>

                <Card class="p-10">
                    <div class="mb-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="text-xl font-black tracking-tight text-text-primary">Economic registry</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Asset currency</label>
                            <select v-model="form.base_currency" class="w-full appearance-none rounded-2xl border border-panel-line bg-panel-2 p-4 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                                <option value="USD">USD — United States Dollar</option>
                                <option value="BDT">BDT — Bangladeshi Taka</option>
                            </select>
                            <p class="text-[11px] text-text-muted">Stored as a clean ISO code ({{ form.base_currency }}).</p>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-text-muted">Exchange rate (BDT per USD)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-lg font-black text-text-muted">{{ currencyLabel(form.base_currency) }}</span>
                                <input v-model="form.exchange_rate" type="number" step="0.01" class="w-full rounded-2xl border border-panel-line bg-panel-2 p-4 pl-12 text-sm font-bold text-text-primary focus:border-amber focus:outline-none">
                            </div>
                            <p class="text-[11px] text-text-muted">Synced to the <span class="font-mono">exchange_rates</span> table and cache on save.</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- RSA signing architecture -->
            <Card class="p-10">
                <div class="mb-8 flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber/10 text-amber">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    </div>
                    <h3 class="text-xl font-black tracking-tight text-text-primary">RSA cryptographic signing</h3>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-panel-line bg-panel-2 p-5">
                        <p class="mb-1 text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Algorithm</p>
                        <p class="font-mono text-sm font-black text-text-primary">{{ signing.algorithm || 'RSA-SHA256' }}</p>
                    </div>
                    <div class="rounded-2xl border border-panel-line bg-panel-2 p-5">
                        <p class="mb-1 text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Active key ID</p>
                        <p class="break-all font-mono text-sm font-black text-text-primary">{{ signing.active_key_id || 'Not configured' }}</p>
                    </div>
                    <div class="rounded-2xl border border-panel-line bg-panel-2 p-5">
                        <p class="mb-1 text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Offline validity</p>
                        <p class="text-sm font-black text-text-primary">{{ protocol.offline_validity_days || 7 }} days</p>
                    </div>
                    <div class="rounded-2xl border border-panel-line bg-panel-2 p-5">
                        <p class="mb-1 text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Heartbeat pulse</p>
                        <p class="text-sm font-black text-text-primary">{{ protocol.pulse_interval_days || 30 }} days</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-panel-line bg-panel-2 p-5">
                    <div class="flex items-center gap-3">
                        <span class="h-2 w-2 rounded-full" :class="publicKeyConfigured ? 'bg-teal' : 'bg-danger'" />
                        <div>
                            <p class="text-sm font-black text-text-primary">Public key {{ publicKeyConfigured ? 'active' : 'unavailable' }}</p>
                            <p class="text-[11px] text-text-muted">Served for offline signature verification.</p>
                        </div>
                    </div>
                    <a :href="signing.public_key_url || '/api/v1/license/public-key'" target="_blank" rel="noopener" class="text-xs font-black uppercase tracking-[0.18em] text-amber hover:underline">
                        Download public key →
                    </a>
                </div>
            </Card>

            <!-- System infrastructure status -->
            <Card class="p-10">
                <div class="mb-8 flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-teal/10 text-teal">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                    </div>
                    <h3 class="text-xl font-black tracking-tight text-text-primary">System infrastructure</h3>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="badge in infraBadges" :key="badge.label" class="rounded-2xl border border-panel-line bg-panel-2 p-5">
                        <p class="mb-1 text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">{{ badge.label }}</p>
                        <div class="flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-teal" />
                            <p class="break-all font-mono text-sm font-black text-text-primary">{{ badge.value }}</p>
                        </div>
                    </div>
                </div>
            </Card>

            <div class="flex justify-end pt-4">
                <Button type="button" variant="primary" :disabled="saving" @click="saveSettings">
                    {{ saving ? 'Syncing...' : 'Commit changes' }}
                </Button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
