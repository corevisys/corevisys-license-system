<script setup>
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import StepList from '@/Components/public/StepList.vue';
import BenefitCard from '@/Components/public/BenefitCard.vue';
import CodeBlock from '@/Components/public/CodeBlock.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
import PageToc from '@/Components/public/PageToc.vue';
import Reveal from '@/Components/public/Reveal.vue';
import Badge from '@/Components/UI/Badge.vue';
import {
    activationExample,
    apiEndpoints,
    developerNotes,
    enforcementModes,
    offlineExample,
    offlineFacts,
    offlineSteps,
    site,
} from '@/content/public.js';

const methodStatus = (method) => (method === 'GET' ? 'success' : 'amber');

const toc = [
    { id: 'endpoints', label: 'API reference' },
    { id: 'activation', label: 'Activation' },
    { id: 'offline', label: 'Offline verification' },
    { id: 'enforcement', label: 'Enforcement modes' },
    { id: 'notes', label: 'Implementation notes' },
];
</script>

<template>
    <PublicLayout title="Developers" :description="'API and offline verification documentation for ' + site.product + '.'">
        <!-- Header -->
        <section class="relative overflow-hidden border-b border-panel-line">
            <span class="bg-grid pointer-events-none absolute inset-0 opacity-40" aria-hidden="true" />
            <span class="hero-glow pointer-events-none absolute -top-24 left-1/3 h-72 w-72 rounded-full" aria-hidden="true" />

            <div class="relative mx-auto max-w-6xl px-5 pb-14 pt-16 sm:pt-20">
                <SectionHeading
                    eyebrow="Developers"
                    title="Activate, check, and verify offline"
                    description="All license endpoints live under /api/v1. Activation and checks are throttled, and every response that carries license state is signed so your application can verify it without a network round-trip."
                />
            </div>
        </section>

        <div class="mx-auto max-w-6xl px-5 py-16">
            <div class="grid gap-12 lg:grid-cols-[220px_1fr]">
                <!-- Sticky TOC -->
                <aside class="hidden lg:block">
                    <PageToc :items="toc" />
                </aside>

                <div class="min-w-0 space-y-20">
                    <!-- Endpoints -->
                    <section id="endpoints" class="scroll-mt-24">
                        <SectionHeading eyebrow="Endpoints" title="API reference" />
                        <Reveal class="mt-8 grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="endpoint in apiEndpoints"
                                :key="endpoint.path"
                                class="card-lift rounded-2xl border border-panel-line bg-panel p-5"
                            >
                                <div class="flex items-center gap-3">
                                    <Badge :status="methodStatus(endpoint.method)" :dot="false">{{ endpoint.method }}</Badge>
                                    <code class="truncate font-mono text-[12.5px] text-text-primary">{{ endpoint.path }}</code>
                                </div>
                                <p class="mt-3 text-sm leading-relaxed text-text-secondary">{{ endpoint.summary }}</p>
                                <p class="mt-3 font-mono text-[11px] uppercase tracking-wide text-text-muted">Auth: {{ endpoint.auth }}</p>
                            </div>
                        </Reveal>
                    </section>

                    <!-- Activation -->
                    <section id="activation" class="scroll-mt-24">
                        <SectionHeading
                            eyebrow="Activation"
                            title="Request and signed response"
                            description="Send the license key and fingerprint, then store the signed response for offline checks."
                        />
                        <div class="mt-8 grid gap-6 lg:grid-cols-2">
                            <CodeBlock heading="Request" caption="POST /api/v1/license/activate">
                                <template #default><span>{{ activationExample.request }}</span></template>
                            </CodeBlock>
                            <CodeBlock heading="Signed response" caption="The payload and signature are returned on every state response.">
                                <template #default><span>{{ activationExample.response }}</span></template>
                            </CodeBlock>
                        </div>
                    </section>

                    <!-- Offline -->
                    <section id="offline" class="scroll-mt-24">
                        <SectionHeading
                            eyebrow="Offline verification"
                            title="Trust a response without the network"
                            description="Responses are canonicalised, then signed with RSA-SHA256. Cache the public key and a recent signed response to keep working offline."
                        />
                        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                            <div>
                                <dl class="grid gap-3 sm:grid-cols-2">
                                    <div v-for="fact in offlineFacts" :key="fact.label" class="rounded-2xl border border-panel-line bg-panel p-[14px_16px]">
                                        <dt class="font-mono text-[11px] uppercase tracking-wide text-text-muted">{{ fact.label }}</dt>
                                        <dd class="mt-1.5 text-[13.5px] text-text-primary">{{ fact.value }}</dd>
                                    </div>
                                </dl>
                                <div class="mt-6">
                                    <CodeBlock heading="Shell" caption="Verify the base64 payload against the public key.">
                                        <template #default><span>{{ offlineExample }}</span></template>
                                    </CodeBlock>
                                </div>
                            </div>
                            <StepList :steps="offlineSteps" />
                        </div>
                    </section>

                    <!-- Enforcement -->
                    <section id="enforcement" class="scroll-mt-24">
                        <SectionHeading
                            eyebrow="Enforcement modes"
                            title="Choose how strictly to bind"
                            description="Pass enforcement_mode on activate, check, or pulse to control how fingerprint mismatches are handled."
                        />
                        <Reveal class="mt-8 grid gap-4 sm:grid-cols-3">
                            <BenefitCard
                                v-for="mode in enforcementModes"
                                :key="mode.name"
                                :title="mode.name"
                                :body="`${mode.summary} ${mode.detail}`"
                            />
                        </Reveal>
                    </section>

                    <!-- Notes -->
                    <section id="notes" class="scroll-mt-24">
                        <SectionHeading eyebrow="Good to know" title="Implementation notes" />
                        <ul class="mt-8 space-y-3">
                            <li
                                v-for="note in developerNotes"
                                :key="note"
                                class="flex gap-3 rounded-2xl border border-panel-line bg-panel p-[16px_18px] text-sm text-text-secondary"
                            >
                                <span class="font-mono text-brand-primary" aria-hidden="true">→</span>
                                <span>{{ note }}</span>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>

        <section class="mx-auto max-w-6xl px-5 pb-20">
            <CTABanner
                title="Need an API token or a sandbox key?"
                body="Create an account to generate a license and API token, or contact us for integration questions."
                primary-label="Get started"
                primary-href="/pricing"
                secondary-label="Contact us"
                secondary-href="/contact"
            />
        </section>
    </PublicLayout>
</template>