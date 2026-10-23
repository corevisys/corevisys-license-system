<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import TerminalBlock from '@/Components/UI/TerminalBlock.vue';
import Alert from '@/Components/UI/Alert.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { contact } from '@/content/public.js';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    license: Object,
    api_base_url: String,
    public_key_url: String,
    offline_validity_days: Number,
    pulse_interval_days: Number,
});

const licenseKey = ref('');
const keyLoading = ref(true);
const keyMessage = ref('');
const keyMessageIsError = ref(false);

const loadLicenseKey = async () => {
    keyLoading.value = true;
    keyMessage.value = '';
    keyMessageIsError.value = false;

    try {
        const response = await axios.post(route('licenses.reveal', props.license.id));
        const key = response.data?.license_key;

        if (typeof key !== 'string' || key.trim() === '') {
            throw new Error('License key unavailable. Contact support to request a reissue.');
        }

        licenseKey.value = key;
    } catch (error) {
        licenseKey.value = '';
        keyMessageIsError.value = true;
        keyMessage.value = error.response?.data?.message ?? error.message ?? 'Unable to retrieve the license key. Try again later.';
    } finally {
        keyLoading.value = false;
    }
};

onMounted(loadLicenseKey);

const statusVariant = (status) => {
    const value = String(status || '').toLowerCase();
    const mapping = {
        inactive: 'default',
        active: 'success',
        expired: 'danger',
        suspended: 'danger',
        revoked: 'danger',
    };

    return mapping[value] ?? 'default';
};

const copyToClipboard = async () => {
    keyMessage.value = '';
    keyMessageIsError.value = false;

    if (!licenseKey.value) {
        keyMessageIsError.value = true;
        keyMessage.value = 'License key is unavailable and cannot be copied.';
        return;
    }

    try {
        await navigator.clipboard.writeText(licenseKey.value);
        keyMessage.value = 'License key copied to clipboard.';
    } catch {
        keyMessageIsError.value = true;
        keyMessage.value = 'Unable to copy the license key. Check your browser clipboard permissions.';
    }
};

// --- Documentation helpers -------------------------------------------------

const enforcementLabel = computed(() => {
    const mode = String(props.license.enforcement_mode || 'standard');
    return mode.charAt(0).toUpperCase() + mode.slice(1);
});

const features = computed(() =>
    Array.isArray(props.license.features) ? props.license.features : [],
);

const supportEmail = contact.supportEmail;

const resetRequestHref = computed(() => {
    const subject = encodeURIComponent(
        `License reset request — ${props.license.product_name} (ID ${props.license.id})`,
    );
    const body = encodeURIComponent(
        `Please reset the domain / seat binding for the following license so I can migrate it.\n\n` +
        `License ID: ${props.license.id}\n` +
        `Product: ${props.license.product_name}\n` +
        `Product code: ${props.license.product_code || 'n/a'}\n` +
        `Bound domain: ${props.license.bound_domain || 'unbound'}\n` +
        `Bound IP: ${props.license.bound_ip || 'unbound'}\n\n` +
        `Reason: `,
    );
    return `mailto:${supportEmail}?subject=${subject}&body=${body}`;
});

const supportHref = `mailto:${supportEmail}`;

// Core API endpoints exposed by the verification system.
const endpoints = [
    {
        key: 'activate',
        method: 'POST',
        path: '/license/activate',
        title: 'Activation',
        desc: 'Initial binding of the license to a domain, IP and optional hardware fingerprint. This consumes a seat.',
    },
    {
        key: 'pulse',
        method: 'POST',
        path: '/license/pulse',
        title: 'Heartbeat Pulse',
        desc: 'Periodic check-in (every pulse interval) that keeps the license marked as live and refreshes the offline grace window.',
    },
    {
        key: 'check',
        method: 'POST',
        path: '/license/check',
        title: 'Status Check',
        desc: 'Read-only validation with no side effects — use it to verify a license without consuming or re-binding a seat.',
    },
    {
        key: 'deactivate',
        method: 'POST',
        path: '/license/deactivate',
        title: 'Deactivation',
        desc: 'Release the current seat so the domain can be migrated. Requires the fingerprint when the license is fingerprint-bound.',
    },
    {
        key: 'public-key',
        method: 'GET',
        path: '/license/public-key',
        title: 'Public Key',
        desc: 'Fetch the RSA public key used to verify signed offline license responses locally.',
    },
];

const activeEndpoint = ref('activate');
const setActiveEndpoint = (key) => {
    activeEndpoint.value = key;
};

// Example request body for the activation endpoint, pre-filled with live data.
const activationPayload = computed(() => ({
    license_key: licenseKey.value || 'YOUR_LICENSE_KEY',
    product_code: props.license.product_code || 'your-product-code',
    domain: props.license.bound_domain || 'yourdomain.com',
    ip: props.license.bound_ip || '8.8.8.8',
    fingerprint: props.license.bound_fingerprint || 'optional-machine-fingerprint',
    enforcement_mode: props.license.enforcement_mode || 'standard',
}));

const activateUrl = computed(() => `${props.api_base_url}/license/activate`);

const curlSnippet = computed(() => {
    const body = JSON.stringify(activationPayload.value, null, 2);
    return `curl -X POST ${activateUrl.value} \\
  -H "Content-Type: application/json" \\
  -H "Accept: application/json" \\
  -H "X-API-Version: 1.0" \\
  -d '${body}'`;
});

const phpSnippet = computed(() => {
    const body = JSON.stringify(activationPayload.value, null, 2);
    return `<?php

$payload = json_decode(<<<'JSON'
${body}
JSON, true);

$ch = curl_init('${activateUrl.value}');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-API-Version: 1.0',
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
]);

$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (empty($data['status'])) {
    throw new \\RuntimeException($data['message'] ?? 'Activation failed');
}`;
});

const jsSnippet = computed(() => {
    const body = JSON.stringify(activationPayload.value, null, 2);
    return `const res = await fetch('${activateUrl.value}', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-API-Version': '1.0',
  },
  body: JSON.stringify(${body}),
});

const data = await res.json();

if (!data.status) {
  throw new Error(data.message || 'Activation failed');
}`;
});

const snippetTabs = computed(() => [
    { id: 'curl', label: 'cURL', code: curlSnippet.value },
    { id: 'php', label: 'PHP', code: phpSnippet.value },
    { id: 'js', label: 'JavaScript / Node', code: jsSnippet.value },
]);

const activeSnippet = ref('curl');
const copiedSnippet = ref('');

const activeSnippetCode = computed(
    () => snippetTabs.value.find((tab) => tab.id === activeSnippet.value)?.code ?? '',
);

const copyCode = async () => {
    keyMessage.value = '';
    keyMessageIsError.value = false;

    try {
        await navigator.clipboard.writeText(activeSnippetCode.value);
        copiedSnippet.value = activeSnippet.value;
        keyMessage.value = 'Code snippet copied to clipboard.';
        setTimeout(() => {
            if (copiedSnippet.value === activeSnippet.value) {
                copiedSnippet.value = '';
            }
        }, 2000);
    } catch {
        keyMessageIsError.value = true;
        keyMessage.value = 'Unable to copy the code snippet. Check your browser clipboard permissions.';
    }
};
</script>

<template>
    <Head :title="'Configure - ' + license.product_name" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-5xl py-12">
            <Alert v-if="keyMessage" :variant="keyMessageIsError ? 'danger' : 'success'" class="mb-6">
                {{ keyMessage }}
            </Alert>

            <div class="mb-12 flex items-center justify-between gap-4">
                <div>
                    <div class="mb-3 flex items-center gap-4">
                        <Link :href="route('licenses')" class="rounded-xl border border-panel-line p-2 text-text-muted transition hover:border-amber hover:text-amber">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                        </Link>
                        <h2 class="text-3xl font-black tracking-tight text-text-primary">License <span class="text-teal">configuration</span></h2>
                    </div>
                    <p class="text-sm text-text-muted">Integration guide and security settings for {{ license.product_name }}.</p>
                </div>
                <Badge :status="statusVariant(license.status)">{{ license.status }}</Badge>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <div class="space-y-8 lg:col-span-2">
                    <Card>
                        <h3 class="mb-6 flex items-center gap-3 text-lg font-black text-text-primary">
                            <svg class="h-5 w-5 text-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                            Access credentials
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">Your license key</label>
                                <div class="flex items-center gap-3">
                                    <TerminalBlock class="min-w-0 flex-1 break-all px-4 py-3">
                                        {{ licenseKey || (keyLoading ? 'Loading license key...' : 'License key unavailable') }}
                                    </TerminalBlock>
                                    <Button type="button" variant="secondary" :disabled="keyLoading || !licenseKey" @click="copyToClipboard">Copy</Button>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <div>
                                    <p class="text-sm font-bold text-text-primary">Need to move this license?</p>
                                    <p class="mt-0.5 text-xs text-text-muted">Domain and seat resets are an administrator action. Request one through support.</p>
                                </div>
                                <a :href="resetRequestHref">
                                    <Button type="button" variant="secondary">Request domain / seat reset</Button>
                                </a>
                            </div>
                        </div>
                    </Card>

                    <Card>
                        <h3 class="mb-8 text-xl font-black text-text-primary">Integration guide</h3>

                        <div class="space-y-10">
                            <div class="flex gap-6">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-lg font-black text-teal">1</div>
                                <div>
                                    <h4 class="mb-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">Initialize activation</h4>
                                    <p class="mb-4 text-sm leading-relaxed text-text-muted">Send a POST request to our activation endpoint with your license key. This links your current environment (domain / IP / fingerprint) to our system.</p>
                                    <TerminalBlock>
                                        <div class="mb-1 text-amber">Method: <span class="text-text-primary">POST</span></div>
                                        <div class="text-amber">URL: <span class="text-text-secondary">{{ api_base_url }}/license/activate</span></div>
                                    </TerminalBlock>
                                </div>
                            </div>

                            <div class="flex gap-6">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-lg font-black text-teal">2</div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="mb-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">Payload requirements</h4>
                                    <p class="mb-4 text-sm leading-relaxed text-text-muted">
                                        The JSON payload is sent in the <span class="font-bold text-text-primary">request body</span>
                                        (not the header), with the following headers:
                                    </p>
                                    <TerminalBlock class="mb-4 whitespace-pre-wrap break-all">
                                        Content-Type: application/json
                                        Accept: application/json
                                        X-API-Version: 1.0
                                    </TerminalBlock>

                                    <TerminalBlock v-if="licenseKey" class="whitespace-pre-wrap break-all">
                                        {
                                          "license_key": "{{ licenseKey }}",
                                          "product_code": "{{ license.product_code }}",
                                          "domain": "yourdomain.com",
                                          "ip": "8.8.8.8",
                                          "fingerprint": "optional-machine-fingerprint",
                                          "enforcement_mode": "standard"
                                        }
                                    </TerminalBlock>
                                    <p v-else class="text-sm text-text-muted">{{ keyLoading ? 'Loading license key...' : 'License key unavailable.' }}</p>

                                    <ul class="mt-4 space-y-2 text-xs leading-relaxed text-text-muted">
                                        <li><span class="font-mono text-amber">ip</span> — required. The calling server's public IP (note: <span class="font-mono">ip</span>, not <span class="font-mono">ip_address</span>).</li>
                                        <li><span class="font-mono text-amber">product_code</span> — required. Must match the product slug or the request is rejected with 403.</li>
                                        <li><span class="font-mono text-amber">fingerprint</span> — optional. Enables hardware locking for stricter environments.</li>
                                        <li><span class="font-mono text-amber">enforcement_mode</span> — optional. <span class="font-mono">standard</span> or <span class="font-mono">strict</span>.</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="flex gap-6">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-lg font-black text-teal">3</div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="mb-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">Runnable example</h4>
                                    <p class="mb-4 text-sm leading-relaxed text-text-muted">Pre-filled with your license key, product code, and API base URL. Copy and run it as-is.</p>

                                    <div class="mb-3 flex flex-wrap items-center gap-2">
                                        <button
                                            v-for="tab in snippetTabs"
                                            :key="tab.id"
                                            type="button"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-bold transition-colors"
                                            :class="activeSnippet === tab.id ? 'border-amber bg-amber/10 text-amber' : 'border-panel-line bg-panel-2 text-text-muted hover:border-amber/40'"
                                            @click="activeSnippet = tab.id"
                                        >
                                            {{ tab.label }}
                                        </button>
                                        <Button type="button" variant="secondary" class="ml-auto !px-3 !py-1.5 !text-xs" @click="copyCode">
                                            {{ copiedSnippet === activeSnippet ? 'Copied!' : 'Copy code' }}
                                        </Button>
                                    </div>

                                    <TerminalBlock class="overflow-x-auto whitespace-pre text-[12px]">{{ activeSnippetCode }}</TerminalBlock>
                                </div>
                            </div>

                            <div class="flex gap-6">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-lg font-black text-teal">4</div>
                                <div>
                                    <h4 class="mb-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">Validation loop</h4>
                                    <p class="text-sm leading-relaxed text-text-muted">The activation response returns a signed token. Store it locally and re-verify offline using the public key, then send a heartbeat pulse every {{ pulse_interval_days }} days to keep the license live.</p>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card>
                        <h3 class="mb-6 text-xl font-black text-text-primary">API endpoint reference</h3>
                        <p class="mb-6 text-sm text-text-muted">All endpoints live under <span class="font-mono text-text-secondary">{{ api_base_url }}</span> and expect the JSON headers listed above.</p>

                        <div class="space-y-3">
                            <button
                                v-for="endpoint in endpoints"
                                :key="endpoint.key"
                                type="button"
                                class="w-full rounded-2xl border p-4 text-left transition-colors"
                                :class="activeEndpoint === endpoint.key ? 'border-amber bg-amber/10' : 'border-panel-line bg-panel-2 hover:border-amber/40'"
                                @click="setActiveEndpoint(endpoint.key)"
                            >
                                <div class="flex flex-wrap items-center gap-3">
                                    <span class="rounded-md border border-panel-line bg-bg-dark px-2 py-0.5 font-mono text-[10px] font-black uppercase tracking-[0.18em] text-amber">{{ endpoint.method }}</span>
                                    <span class="font-mono text-sm text-text-primary">/api/v1{{ endpoint.path }}</span>
                                    <span class="ml-auto text-xs font-black uppercase tracking-[0.18em] text-teal">{{ endpoint.title }}</span>
                                </div>
                                <p class="mt-2 text-xs leading-relaxed text-text-muted">{{ endpoint.desc }}</p>
                            </button>
                        </div>

                        <div v-if="activeEndpoint === 'public-key'" class="mt-6">
                            <p class="mb-2 text-[10px] font-black uppercase tracking-[0.24em] text-text-muted">Offline verification key</p>
                            <TerminalBlock class="break-all">{{ public_key_url }}</TerminalBlock>
                        </div>
                    </Card>
                </div>

                <div class="space-y-8">
                    <Card>
                        <h3 class="mb-6 flex items-center gap-2 text-sm font-black uppercase tracking-[0.24em] text-text-primary">
                            <span class="h-2 w-2 rounded-full bg-teal" />
                            Environment binding
                        </h3>

                        <div class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                    <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Bound domain</label>
                                    <p class="break-all font-mono text-sm text-text-primary">{{ license.bound_domain || 'Not bound' }}</p>
                                </div>
                                <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                    <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Bound IP</label>
                                    <p class="break-all font-mono text-sm text-text-primary">{{ license.bound_ip || 'Not bound' }}</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Hardware fingerprint</label>
                                <p class="break-all font-mono text-sm text-text-primary">{{ license.bound_fingerprint || 'Unbound / Any' }}</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                    <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Enforcement mode</label>
                                    <p class="text-sm font-black text-text-primary">{{ enforcementLabel }}</p>
                                </div>
                                <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                    <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">License type</label>
                                    <p class="text-sm font-black capitalize text-text-primary">{{ license.license_type || '—' }}</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Activation limit</label>
                                <div class="flex items-center justify-between gap-4">
                                    <p class="text-sm font-black text-text-primary">{{ license.current_usage }} / {{ license.activation_limit }}</p>
                                    <div class="h-1 flex-1 overflow-hidden rounded-full bg-panel-line">
                                        <div class="h-full bg-gradient-to-r from-amber to-teal" :style="{ width: (license.activation_limit > 0 ? (license.current_usage / license.activation_limit * 100) : 0) + '%' }" />
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                    <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Pulse interval</label>
                                    <p class="text-sm font-black text-text-primary">{{ pulse_interval_days }} days</p>
                                </div>
                                <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                    <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Offline grace</label>
                                    <p class="text-sm font-black text-text-primary">{{ offline_validity_days }} days</p>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Last heartbeat</label>
                                <p class="font-mono text-sm text-text-primary">{{ license.last_check_at || 'Never' }}</p>
                            </div>

                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <label class="mb-2 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Features / entitlements</label>
                                <div v-if="features.length" class="flex flex-wrap gap-2">
                                    <span
                                        v-for="feature in features"
                                        :key="feature"
                                        class="rounded-full border border-teal/30 bg-teal/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-teal"
                                    >
                                        {{ feature }}
                                    </span>
                                </div>
                                <p v-else class="text-xs text-text-muted">No additional features assigned.</p>
                            </div>
                        </div>
                    </Card>

                    <div class="rounded-[32px] bg-gradient-to-br from-amber/90 to-teal p-8 text-[#120f0d]">
                        <h3 class="mb-2 text-lg font-black">Need help with integration?</h3>
                        <p class="mb-6 text-xs font-bold uppercase tracking-[0.2em] text-[#2a1d12]">Support available 24/7</p>
                        <div class="flex flex-col gap-3">
                            <a :href="supportHref" class="block">
                                <Button type="button" variant="secondary" class="w-full justify-center">Contact support</Button>
                            </a>
                            <a :href="resetRequestHref" class="block">
                                <Button type="button" variant="secondary" class="w-full justify-center">Request domain / seat reset</Button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
