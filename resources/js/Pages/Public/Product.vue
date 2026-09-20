<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import StepList from '@/Components/public/StepList.vue';
import Timeline from '@/Components/public/Timeline.vue';
import BenefitCard from '@/Components/public/BenefitCard.vue';
import FAQ from '@/Components/public/FAQ.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
import Reveal from '@/Components/public/Reveal.vue';
import Alert from '@/Components/UI/Alert.vue';
import Badge from '@/Components/UI/Badge.vue';
import {
    activationFacts,
    lifecycle,
    licenseNotes,
    paymentFlow,
    productFaqs,
    productFeaturesPlaceholder,
    productPage,
    productPaymentNote,
    site,
} from '@/content/public.js';

const props = defineProps({
    product: { type: Object, required: true },
    otherProducts: { type: Array, default: () => [] },
    gatewaySettings: { type: Object, default: () => ({}) },
});

const page = usePage();
const isAuthed = computed(() => Boolean(page.props.auth?.user));

// Existing checkout / trial flows live in the store; guests sign up first.
const storeHref = computed(() => (isAuthed.value ? route('store') : route('register')));

const prices = computed(() => props.product.prices ?? []);

// Only offer the currencies this product is actually priced in.
const currencies = computed(() => [...new Set(prices.value.map((price) => price.currency))]);
const activeCurrency = ref(currencies.value[0] ?? null);
const showCurrencyToggle = computed(() => currencies.value.length > 1);
const visiblePrices = computed(() =>
    activeCurrency.value
        ? prices.value.filter((price) => price.currency === activeCurrency.value)
        : prices.value,
);

const formatAmount = (amount) =>
    Number(amount).toLocaleString(undefined, {
        minimumFractionDigits: Number(amount) % 1 === 0 ? 0 : 2,
        maximumFractionDigits: 2,
    });

const billingLabel = (period) =>
    !period ? productPage.oneTime : period >= 365 ? productPage.yearly : productPage.monthly;

const startingAmount = computed(() => {
    if (visiblePrices.value.length === 0) return null;
    return visiblePrices.value.reduce((min, price) =>
        Number(price.amount) < Number(min.amount) ? price : min,
    );
});

const startingLabel = computed(() => {
    if (!startingAmount.value) return null;
    if (Number(startingAmount.value.amount) === 0) return 'Free';
    return `${formatAmount(startingAmount.value.amount)} ${startingAmount.value.currency}`;
});

// A trial button is only shown when the product actually has a trial price.
const supportsTrial = computed(() => prices.value.some((price) => price.type === 'trial'));

const otherList = computed(() =>
    props.otherProducts.filter((item) => item.slug && item.slug !== props.product.slug).slice(0, 2),
);

const enabledGateways = computed(() => {
    const list = [];
    if (props.gatewaySettings.gateway_stripe_active === '1') list.push('Card (Stripe)');
    if (props.gatewaySettings.gateway_bkash_active === '1') list.push('bKash');
    list.push('Offline bank transfer');
    return list;
});

const pageTitle = computed(() => `${props.product.name} pricing`);
const metaDescription = computed(() =>
    (
        props.product.description ||
        `${props.product.name} plans, activation, and licensing from ${site.name}.`
    ).slice(0, 300),
);

const canonical = computed(() => {
    const origin = typeof window !== 'undefined' ? window.location.origin : '';
    return `${origin}/pricing/${props.product.slug}`;
});

// JSON-LD is only emitted when real pricing data exists (no invented offers).
const jsonLd = computed(() => {
    const offer = startingAmount.value;
    if (!offer || Number(offer.amount) <= 0) return null;

    const data = {
        '@context': 'https://schema.org',
        '@type': 'Product',
        name: props.product.name,
        url: canonical.value,
        offers: {
            '@type': 'Offer',
            price: Number(offer.amount),
            priceCurrency: offer.currency,
            availability: 'https://schema.org/InStock',
            url: canonical.value,
        },
    };

    if (props.product.description) {
        data.description = props.product.description;
    }

    return JSON.stringify(data).replace(/</g, '\\u003c');
});

// Icon per license note, matched by title so copy stays the single source of truth.
const noteIcons = {
    'Offline validity window': 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    'Offline signing': 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z',
    'Grace period': 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    'Key storage': 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
};
const noteIcon = (title) => noteIcons[title] ?? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
</script>

<template>
    <PublicLayout :title="pageTitle" :description="metaDescription">
        <Head>
            <link rel="canonical" :href="canonical" />
            <meta property="og:type" content="product" />
            <meta property="og:title" :content="pageTitle" />
            <meta property="og:description" :content="metaDescription" />
            <meta property="og:url" :content="canonical" />
            <meta name="twitter:card" content="summary" />
            <component
                :is="'script'"
                v-if="jsonLd"
                type="application/ld+json"
                head-key="product-jsonld"
            >{{ jsonLd }}</component>
        </Head>

        <!-- Header band -->
        <section class="relative overflow-hidden border-b border-panel-line">
            <span class="bg-grid pointer-events-none absolute inset-0 opacity-40" aria-hidden="true" />
            <span class="hero-glow pointer-events-none absolute -top-24 right-1/4 h-72 w-72 rounded-full" aria-hidden="true" />

            <div class="relative mx-auto max-w-6xl px-5 pb-14 pt-14">
                <nav aria-label="Breadcrumb" class="font-mono text-[11.5px] uppercase tracking-widest text-text-muted">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li>
                            <Link :href="route('pricing')" class="transition-colors hover:text-brand-primary">{{ productPage.breadcrumbPricing }}</Link>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li class="text-text-secondary">{{ product.name }}</li>
                    </ol>
                </nav>

                <div class="mt-8 grid gap-10 lg:grid-cols-[1.15fr_1fr]">
                    <div>
                        <Badge status="amber">Product</Badge>
                        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-text-primary sm:text-5xl">
                            {{ product.name }}
                        </h1>
                        <p v-if="product.description" class="mt-4 max-w-2xl text-base leading-relaxed text-text-secondary">
                            {{ product.description }}
                        </p>

                        <div class="mt-7 flex flex-wrap gap-3">
                            <Link
                                :href="storeHref"
                                class="inline-flex items-center rounded-lg border border-transparent bg-brand-primary px-[18px] py-[11px] text-[14.5px] font-medium text-bg-dark transition-transform hover:scale-[1.03] motion-reduce:hover:scale-100"
                            >{{ productPage.buyLabel }}</Link>
                            <Link
                                v-if="supportsTrial"
                                :href="storeHref"
                                class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-primary transition-colors hover:border-brand-primary/40"
                            >{{ productPage.trialLabel }}</Link>
                            <Link
                                v-else
                                :href="route('contact')"
                                class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-primary transition-colors hover:border-brand-primary/40"
                            >{{ productPage.askTrialLabel }}</Link>
                        </div>

                        <p class="mt-4 max-w-xl text-[12.5px] leading-relaxed text-text-secondary">{{ productPage.checkoutNote }}</p>
                        <p v-if="supportsTrial" class="mt-1 max-w-xl text-[12.5px] leading-relaxed text-text-secondary">
                            {{ productPage.trialNote }}
                        </p>
                    </div>

                    <!-- Sticky price card -->
                    <div class="lg:sticky lg:top-24 lg:self-start">
                        <div class="card-lift rounded-3xl border border-panel-line bg-panel p-6">
                            <p class="font-mono text-[11px] uppercase tracking-widest text-text-muted">{{ productPage.startingFrom }}</p>
                            <p class="mt-2 font-mono text-4xl font-semibold tracking-tight text-text-primary">{{ startingLabel || '\u2014' }}</p>

                            <div v-if="showCurrencyToggle" class="mt-5 inline-flex items-center rounded-full border border-panel-line bg-panel-2 p-1" role="group" aria-label="Currency">
                                <button
                                    v-for="currency in currencies"
                                    :key="currency"
                                    type="button"
                                    class="rounded-full px-3.5 py-1.5 font-mono text-[12px] transition-colors"
                                    :class="currency === activeCurrency
                                        ? 'bg-brand-primary/15 text-brand-primary'
                                        : 'text-text-secondary hover:text-text-primary'"
                                    :aria-pressed="currency === activeCurrency"
                                    @click="activeCurrency = currency"
                                >{{ currency }}</button>
                            </div>

                            <dl class="mt-6 grid gap-px overflow-hidden rounded-2xl border border-panel-line bg-panel-line sm:grid-cols-2">
                                <div v-for="fact in activationFacts.slice(0, 6)" :key="fact.label" class="bg-panel-2 p-[14px_16px]">
                                    <dt class="font-mono text-[10.5px] uppercase tracking-wide text-text-muted">{{ fact.label }}</dt>
                                    <dd class="mt-1.5 text-[13px] font-medium text-text-primary">{{ fact.value }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="mx-auto max-w-6xl px-5 py-20">
            <SectionHeading
                eyebrow="Product"
                :title="productPage.featuresHeading"
                description="What is included is taken from the stored product data — nothing is invented."
            />
            <div class="mt-10 rounded-2xl border border-dashed border-panel-line bg-panel-2 p-6">
                <p class="font-mono text-[13px] text-text-primary">{{ productFeaturesPlaceholder }}</p>
                <p class="mt-2 text-sm leading-relaxed text-text-primary">{{ productPage.featuresEmptyNote }}</p>
            </div>
        </section>

        <!-- Plans -->
        <section class="border-y border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-20">
                <SectionHeading
                    eyebrow="Plans"
                    :title="productPage.plansHeading"
                    description="Billing cycles are exactly as stored on each price. Toggle the currency to see other available prices."
                />

                <p v-if="visiblePrices.length === 0" class="mt-8 text-sm text-text-secondary">
                    {{ productPage.plansEmptyNote }}
                </p>

                <Reveal v-else class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="price in visiblePrices"
                        :key="price.id"
                        class="card-lift flex h-full flex-col rounded-2xl border border-panel-line bg-panel-2 p-6"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-base font-semibold capitalize text-text-primary">{{ price.type }}</h3>
                            <span class="rounded-full border border-panel-line px-2.5 py-0.5 font-mono text-[10.5px] uppercase tracking-wide text-text-primary">{{ billingLabel(price.billing_period) }}</span>
                        </div>
                        <p class="mt-5 flex items-baseline gap-1.5">
                            <span class="font-mono text-4xl font-semibold tracking-tight text-text-primary">{{ formatAmount(price.amount) }}</span>
                            <span class="text-[12px] text-text-primary">{{ price.currency }}</span>
                        </p>
                        <div class="mt-auto pt-6">
                            <Link
                                :href="storeHref"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-transparent bg-brand-primary px-[18px] py-[11px] text-[14.5px] font-medium text-bg-dark transition-transform hover:scale-[1.02] motion-reduce:hover:scale-100"
                            >{{ productPage.buyLabel }}</Link>
                        </div>
                    </div>
                </Reveal>
            </div>
        </section>

        <!-- Lifecycle -->
        <section class="mx-auto max-w-6xl px-5 py-20">
            <SectionHeading
                eyebrow="License model"
                :title="productPage.licenceHeading"
                description="The same lifecycle runs for every product, whether the customer pays online or by bank transfer."
            />
            <div class="mt-10">
                <Timeline :steps="lifecycle.slice(0, 4)" />
            </div>
        </section>

        <!-- Details -->
        <section class="border-y border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-20">
                <SectionHeading
                    eyebrow="Details"
                    :title="productPage.notesHeading"
                    description="The practical rules that apply to this license once it is issued."
                />
                <Reveal class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <BenefitCard v-for="note in licenseNotes" :key="note.title" :title="note.title" :body="note.body">
                        <template #icon>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="noteIcon(note.title)" />
                            </svg>
                        </template>
                    </BenefitCard>
                </Reveal>
            </div>
        </section>

        <!-- Payment -->
        <section class="mx-auto max-w-6xl px-5 py-20">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                <div>
                    <SectionHeading
                        eyebrow="Payment"
                        :title="productPage.paymentHeading"
                        description="Which gateways are live depends on this deployment's settings."
                    />
                    <div class="mt-6 flex flex-wrap items-center gap-2">
                        <span class="font-mono text-[11.5px] uppercase tracking-widest text-text-muted">Payment methods:</span>
                        <Badge v-for="gateway in enabledGateways" :key="gateway" status="success">{{ gateway }}</Badge>
                    </div>
                    <Alert class="mt-5">{{ productPaymentNote }}</Alert>
                </div>
                <StepList :steps="paymentFlow" />
            </div>
        </section>

        <!-- FAQ -->
        <section class="border-y border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-20">
                <SectionHeading eyebrow="FAQ" :title="productPage.faqHeading" align="center" />
                <div class="mx-auto mt-10 max-w-3xl">
                    <FAQ :items="productFaqs" />
                </div>
            </div>
        </section>

        <!-- Other products -->
        <section v-if="otherList.length" class="mx-auto max-w-6xl px-5 py-20">
            <SectionHeading eyebrow="Catalogue" :title="productPage.otherHeading" :description="productPage.otherNote" />
            <div class="mt-10 grid gap-5 md:grid-cols-2">
                <div
                    v-for="item in otherList"
                    :key="item.id"
                    class="card-lift flex h-full flex-col rounded-2xl border border-panel-line bg-panel p-6"
                >
                    <h3 class="text-base font-semibold text-text-primary">{{ item.name }}</h3>
                    <p v-if="item.description" class="mt-2 text-sm leading-relaxed text-text-secondary">
                        {{ item.description }}
                    </p>
                    <div class="mt-auto pt-5">
                        <Link
                            :href="`/pricing/${item.slug}`"
                            class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[16px] py-[9px] text-[13.5px] font-medium text-text-primary transition-colors hover:border-brand-primary/40"
                        >View {{ item.name }}</Link>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <Link :href="route('pricing')" class="text-sm font-medium text-brand-primary hover:underline">{{ productPage.otherLink }}</Link>
            </div>
        </section>

        <!-- CTA -->
        <section class="mx-auto max-w-6xl px-5 pb-20">
            <CTABanner
                :title="`Ready to license ${product.name}?`"
                body="Choose a plan, finish checkout, and activate against your domain in minutes."
                :primary-label="productPage.buyLabel"
                :primary-href="storeHref"
                secondary-label="Contact sales"
                :secondary-href="route('contact')"
            />
        </section>
    </PublicLayout>
</template>