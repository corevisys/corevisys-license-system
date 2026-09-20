<script setup>
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import StepList from '@/Components/public/StepList.vue';
import BenefitCard from '@/Components/public/BenefitCard.vue';
import CodeBlock from '@/Components/public/CodeBlock.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
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
</script>

<template>
    <PublicLayout title="Developers" :description="'API and offline verification documentation for ' + site.product + '.'">
        <section class="mx-auto max-w-6xl px-5 pb-10 pt-14">
            <SectionHeading
                eyebrow="Developers"
                title="Activate, check, and verify offline"
                description="All license endpoints live under /api/v1. Activation and checks are throttled, and every response that carries license state is signed so your application can verify it without a network round-trip."
            />
        </section>

        <section class="mx-auto max-w-6xl px-5 py-8">
            <SectionHeading eyebrow="Endpoints" title="API reference" />
            <div class="mt-7 overflow-x-auto rounded-[10px] border border-panel-line">
                <table class="w-full min-w-[680px] border-collapse text-left text-[13.5px]">
                    <thead>
                        <tr class="bg-panel-2">
                            <th class="border-b border-panel-line px-4 py-3 font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Method</th>
                            <th class="border-b border-panel-line px-4 py-3 font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Path</th>
                            <th class="border-b border-panel-line px-4 py-3 font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Auth</th>
                            <th class="border-b border-panel-line px-4 py-3 font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="endpoint in apiEndpoints" :key="endpoint.path" class="odd:bg-panel-2/40">
                            <td class="border-b border-panel-line px-4 py-3">
                                <Badge :status="methodStatus(endpoint.method)" :dot="false">{{ endpoint.method }}</Badge>
                            </td>
                            <td class="border-b border-panel-line px-4 py-3 font-mono text-[12.5px] text-text-primary">{{ endpoint.path }}</td>
                            <td class="border-b border-panel-line px-4 py-3 text-text-secondary">{{ endpoint.auth }}</td>
                            <td class="border-b border-panel-line px-4 py-3 text-text-secondary">{{ endpoint.summary }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <div class="grid gap-6 lg:grid-cols-2">
                <CodeBlock heading="Request" caption="POST /api/v1/license/activate">
                    <template #default><span>{{ activationExample.request }}</span></template>
                </CodeBlock>
                <CodeBlock heading="Signed response" caption="The payload and signature are returned on every state response.">
                    <template #default><span>{{ activationExample.response }}</span></template>
                </CodeBlock>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                <div>
                    <SectionHeading
                        eyebrow="Offline verification"
                        title="Trust a response without the network"
                        description="Responses are canonicalised, then signed with RSA-SHA256. Cache the public key and a recent signed response to keep working offline."
                    />
                    <dl class="mt-7 grid gap-3 sm:grid-cols-2">
                        <div v-for="fact in offlineFacts" :key="fact.label" class="rounded-[10px] border border-panel-line bg-panel-2 p-[14px_16px]">
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
                <div>
                    <StepList :steps="offlineSteps" />
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                eyebrow="Enforcement modes"
                title="Choose how strictly to bind"
                description="Pass enforcement_mode on activate, check, or pulse to control how fingerprint mismatches are handled."
            />
            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <BenefitCard
                    v-for="mode in enforcementModes"
                    :key="mode.name"
                    :title="mode.name"
                    :body="`${mode.summary} ${mode.detail}`"
                />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading eyebrow="Good to know" title="Implementation notes" />
            <ul class="mt-7 space-y-3">
                <li
                    v-for="note in developerNotes"
                    :key="note"
                    class="flex gap-3 rounded-[10px] border border-panel-line bg-panel-2 p-[16px_18px] text-[13.5px] text-text-secondary"
                >
                    <span class="text-amber" aria-hidden="true">→</span>
                    <span>{{ note }}</span>
                </li>
            </ul>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
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