<script setup>
import { Link } from '@inertiajs/vue3';
import {
    BadgeCheck,
    CalendarClock,
    Fingerprint,
    Globe,
    Hash,
    KeyRound,
    Lock,
    PenTool,
    ShieldCheck,
    Timer,
} from 'lucide-vue-next';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import Timeline from '@/Components/public/Timeline.vue';
import BenefitCard from '@/Components/public/BenefitCard.vue';
import FAQ from '@/Components/public/FAQ.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
import Reveal from '@/Components/public/Reveal.vue';
import Badge from '@/Components/UI/Badge.vue';
import {
    faqs,
    lifecycle,
    licenseNotes,
    pricingHighlights,
    site,
} from '@/content/public.js';

defineProps({
    featuredProducts: { type: Array, default: () => [] },
});

// Verified facts only (see resources/js/content/public.js).
const trustRow = [
    { icon: Hash, label: 'Keys stored hashed' },
    { icon: PenTool, label: 'Signed offline licenses' },
    { icon: BadgeCheck, label: 'Stripe and bKash' },
];

const facts = [
    { value: '24h', label: 'Offline validity window' },
    { value: '7 days', label: 'Grace period after expiry' },
    { value: 'SHA-256', label: 'Hashed license keys' },
    { value: 'RSA-SHA256', label: 'Signed licenses' },
];

const benefitIcons = [Globe, Fingerprint, ShieldCheck, CalendarClock];
const safeIcons = [Hash, Fingerprint, PenTool, Lock];
</script>

<template>
    <PublicLayout :title="site.name" :description="site.description">
        <!-- Hero -->
        <section class="relative overflow-hidden">
            <span class="bg-grid pointer-events-none absolute inset-0 opacity-70" aria-hidden="true" />
            <span class="hero-glow animate-pulse-glow pointer-events-none absolute -top-24 right-0 h-[28rem] w-[28rem] rounded-full" aria-hidden="true" />

            <div class="relative mx-auto grid max-w-6xl items-center gap-14 px-5 pb-20 pt-20 lg:grid-cols-[1.05fr_1fr] lg:pb-28 lg:pt-24">
                <div>
                    <p class="font-mono text-xs uppercase tracking-widest text-brand-primary">Software licensing platform</p>
                    <h1 class="mt-5 text-4xl font-semibold leading-[1.05] tracking-tight text-text-primary sm:text-5xl lg:text-6xl">
                        Software licensing you can <span class="text-brand-primary">verify</span> — online and offline.
                    </h1>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-text-secondary">
                        {{ site.description }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <Link
                            :href="route('pricing')"
                            class="inline-flex items-center rounded-lg border border-transparent bg-brand-primary px-5 py-3 text-[14.5px] font-medium text-bg-dark transition-transform hover:scale-[1.03] motion-reduce:hover:scale-100"
                        >See pricing</Link>
                        <Link
                            :href="route('developers')"
                            class="inline-flex items-center rounded-lg border border-panel-line bg-panel px-5 py-3 text-[14.5px] font-medium text-text-secondary transition-colors hover:border-brand-primary/40"
                        >Read the API docs</Link>
                    </div>

                    <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-3">
                        <li v-for="item in trustRow" :key="item.label" class="flex items-center gap-2 text-[13px] text-text-secondary">
                            <component :is="item.icon" class="h-4 w-4 text-brand-primary" aria-hidden="true" />
                            {{ item.label }}
                        </li>
                    </ul>
                </div>

                <!-- License card visual (HTML/CSS only, no fake data) -->
                <div class="relative">
                    <span class="hero-glow animate-pulse-glow pointer-events-none absolute inset-0 m-auto h-72 w-72 rounded-full" aria-hidden="true" />
                    <div class="glass animate-float relative rounded-3xl p-6 shadow-soft-md">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary">
                                    <KeyRound class="h-4.5 w-4.5" aria-hidden="true" />
                                </span>
                                <span class="text-sm font-semibold text-text-primary">{{ site.product }}</span>
                            </div>
                            <Badge status="success">Active</Badge>
                        </div>

                        <p class="mt-6 font-mono text-lg tracking-[0.2em] text-text-primary">XXXX-XXXX-XXXX</p>

                        <dl class="mt-6 space-y-3 border-t border-panel-line pt-5">
                            <div class="flex items-center justify-between gap-3">
                                <dt class="flex items-center gap-2 text-[13px] text-text-secondary">
                                    <Globe class="h-4 w-4 text-text-muted" aria-hidden="true" /> Domain bound
                                </dt>
                                <dd class="font-mono text-[12.5px] text-text-primary">app.example.com</dd>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <dt class="flex items-center gap-2 text-[13px] text-text-secondary">
                                    <CalendarClock class="h-4 w-4 text-text-muted" aria-hidden="true" /> Expires
                                </dt>
                                <dd class="font-mono text-[12.5px] text-text-primary">2027-03-01</dd>
                            </div>
                        </dl>

                        <div class="mt-6 rounded-xl border border-panel-line bg-panel-2 p-4 font-mono text-[12px] leading-relaxed text-text-primary">
                            <p><span class="text-brand-primary">$</span> curl -X POST /api/v1/license/activate</p>
                            <p class="mt-1 text-text-muted">→ 200 OK · RSA-SHA256 signed</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Facts strip -->
        <section class="border-y border-panel-line bg-panel">
            <div class="mx-auto grid max-w-6xl gap-px overflow-hidden px-5 py-12 sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="fact in facts" :key="fact.label" class="px-2 py-4 sm:px-6">
                    <p class="font-mono text-3xl font-semibold tracking-tight text-text-primary">{{ fact.value }}</p>
                    <p class="mt-2 text-[13px] text-text-secondary">{{ fact.label }}</p>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section class="mx-auto max-w-6xl px-5 py-20 lg:py-28">
            <Reveal>
                <SectionHeading
                    eyebrow="How it works"
                    title="From checkout to offline verification"
                    description="The same lifecycle runs for every license, whether the customer pays online or by bank transfer."
                />
            </Reveal>
            <Reveal class="mt-12">
                <Timeline :steps="lifecycle.slice(0, 4)" />
            </Reveal>
        </section>

        <!-- Benefits (bento) -->
        <section class="border-y border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-20 lg:py-28">
                <Reveal>
                    <SectionHeading
                        align="center"
                        eyebrow="Why CoreVisys"
                        title="Licensing controls that protect your product"
                        description="Everything below is part of the default license model — no extra integration required."
                    />
                </Reveal>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <Reveal
                        v-for="(item, index) in pricingHighlights"
                        :key="item.title"
                        :delay="index * 60"
                        :class="index === 0 ? 'sm:col-span-2' : ''"
                    >
                        <BenefitCard :title="item.title" :body="item.body" class="h-full">
                            <template #icon>
                                <component :is="benefitIcons[index % benefitIcons.length]" class="h-5 w-5" aria-hidden="true" />
                            </template>
                        </BenefitCard>
                    </Reveal>
                </div>
            </div>
        </section>

        <!-- Safe by design -->
        <section class="mx-auto max-w-6xl px-5 py-20 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <Reveal>
                    <SectionHeading
                        eyebrow="Safe by design"
                        title="Security that holds up without the network"
                        description="Keys are never stored in plain text, bindings are enforced on the server, and every state response is signed so your application can trust it offline."
                    />
                </Reveal>
                <Reveal :delay="80">
                    <div class="overflow-hidden rounded-2xl border border-panel-line bg-panel">
                        <div class="flex items-center gap-2 border-b border-panel-line bg-panel-2 px-4 py-2.5">
                            <span class="flex items-center gap-1.5" aria-hidden="true">
                                <span class="h-2.5 w-2.5 rounded-full border border-panel-line bg-panel" />
                                <span class="h-2.5 w-2.5 rounded-full border border-panel-line bg-panel" />
                                <span class="h-2.5 w-2.5 rounded-full border border-panel-line bg-panel" />
                            </span>
                            <span class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">license model</span>
                        </div>
                        <ul class="divide-y divide-panel-line">
                            <li
                                v-for="(note, index) in licenseNotes.slice(0, 4)"
                                :key="note.title"
                                class="flex gap-3.5 px-5 py-4"
                            >
                                <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-primary/10 text-brand-primary">
                                    <component :is="safeIcons[index % safeIcons.length]" class="h-4 w-4" aria-hidden="true" />
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-text-primary">{{ note.title }}</p>
                                    <p class="mt-1 text-[13px] leading-relaxed text-text-secondary">{{ note.body }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </Reveal>
            </div>
        </section>

        <!-- Pricing teaser -->
        <section v-if="featuredProducts.length" class="border-y border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-20 lg:py-28">
                <Reveal>
                    <SectionHeading
                        eyebrow="Products"
                        title="Pick a product to see its plans"
                        description="Each product page shows its own prices, billing cycles, and the license notes that apply to it."
                    />
                </Reveal>
                <div class="mt-12 grid gap-5 md:grid-cols-3">
                    <Reveal
                        v-for="(product, index) in featuredProducts"
                        :key="product.id"
                        :delay="index * 60"
                    >
                        <Link
                            :href="`/pricing/${product.slug}`"
                            class="card-lift flex h-full flex-col rounded-2xl border border-panel-line bg-panel p-6"
                        >
                            <h3 class="text-base font-semibold text-text-primary">{{ product.name }}</h3>
                            <p v-if="product.description" class="mt-2 text-sm leading-relaxed text-text-secondary">
                                {{ product.description }}
                            </p>
                            <span class="mt-auto pt-6 font-mono text-[12px] text-brand-primary">View plans &rarr;</span>
                        </Link>
                    </Reveal>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="mx-auto max-w-6xl px-5 py-20 lg:py-28">
            <Reveal>
                <SectionHeading align="center" eyebrow="FAQ" title="Questions, answered" />
            </Reveal>
            <Reveal class="mx-auto mt-12 max-w-3xl">
                <FAQ :items="faqs.slice(0, 5)" />
            </Reveal>
        </section>

        <!-- Final CTA -->
        <section class="mx-auto max-w-6xl px-5 pb-20 lg:pb-28">
            <Reveal>
                <CTABanner
                    title="Create an account and issue your first license"
                    body="Start with the pricing page, pick a plan, and activate a license in minutes."
                    primary-label="Get started"
                    :primary-href="route('pricing')"
                    secondary-label="Talk to us"
                    :secondary-href="route('contact')"
                />
            </Reveal>
        </section>
    </PublicLayout>
</template>
