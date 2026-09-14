<script setup>
import Badge from '@/Components/UI/Badge.vue';
import Button from '@/Components/UI/Button.vue';
import Card from '@/Components/UI/Card.vue';
import TerminalBlock from '@/Components/UI/TerminalBlock.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    license: Object,
    api_base_url: String,
});

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

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    alert('Copied to clipboard!');
};
</script>

<template>
    <Head :title="'Configure - ' + license.product_name" />

    <AuthenticatedLayout>
        <div class="mx-auto max-w-5xl py-12">
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
                                    <TerminalBlock class="flex-1 break-all px-4 py-3">
                                        {{ license.license_key }}
                                    </TerminalBlock>
                                    <Button type="button" variant="secondary" @click="copyToClipboard(license.license_key)">Copy</Button>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <Button type="button" variant="danger">Revoke</Button>
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
                                    <p class="mb-4 text-sm leading-relaxed text-text-muted">Send a POST request to our validation endpoint with your license key. This links your current environment (IP/domain) to our system.</p>
                                    <TerminalBlock>
                                        <div class="mb-1 text-amber">Method: <span class="text-text-primary">POST</span></div>
                                        <div class="text-amber">URL: <span class="text-text-secondary">{{ props.api_base_url }}/license/activate</span></div>
                                    </TerminalBlock>
                                </div>
                            </div>

                            <div class="flex gap-6">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-lg font-black text-teal">2</div>
                                <div>
                                    <h4 class="mb-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">Payload requirements</h4>
                                    <p class="mb-4 text-sm leading-relaxed text-text-muted">Include the following JSON payload in your request header:</p>
                                    <TerminalBlock class="whitespace-pre-wrap">
                                        {
                                          "license_key": "{{ license.license_key }}",
                                          "domain": "yourdomain.com",
                                          "ip_address": "8.8.8.8"
                                        }
                                    </TerminalBlock>
                                </div>
                            </div>

                            <div class="flex gap-6">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-teal/10 text-lg font-black text-teal">3</div>
                                <div>
                                    <h4 class="mb-2 text-sm font-black uppercase tracking-[0.2em] text-text-primary">Validation loop</h4>
                                    <p class="text-sm leading-relaxed text-text-muted">Our API returns a signed token. Store this locally and check status periodically without making external calls every time.</p>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <div class="space-y-8">
                    <Card>
                        <h3 class="mb-6 flex items-center gap-2 text-sm font-black uppercase tracking-[0.24em] text-text-primary">
                            <span class="h-2 w-2 rounded-full bg-teal" />
                            Environment binding
                        </h3>

                        <div class="space-y-6">
                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Bound domain</label>
                                <p class="font-mono text-sm text-text-primary">{{ license.bound_domain || 'Not bound' }}</p>
                            </div>
                            <div class="rounded-2xl border border-panel-line bg-panel-2 p-4">
                                <label class="mb-1 block text-[9px] font-black uppercase tracking-[0.2em] text-text-muted">Bound IP</label>
                                <p class="font-mono text-sm text-text-primary">{{ license.bound_ip || 'Not bound' }}</p>
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
                        </div>
                    </Card>

                    <div class="rounded-[32px] bg-gradient-to-br from-amber/90 to-teal p-8 text-[#120f0d]">
                        <h3 class="mb-2 text-lg font-black">Need help with integration?</h3>
                        <p class="mb-6 text-xs font-bold uppercase tracking-[0.2em] text-[#2a1d12]">Support available 24/7</p>
                        <Button type="button" variant="secondary" class="w-full justify-center">Contact support</Button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
