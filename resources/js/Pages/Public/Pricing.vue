<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import StepList from '@/Components/public/StepList.vue';
import ComparisonTable from '@/Components/public/ComparisonTable.vue';
import PricingCard from '@/Components/public/PricingCard.vue';
import BenefitCard from '@/Components/public/BenefitCard.vue';
import FAQ from '@/Components/public/FAQ.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
import Reveal from '@/Components/public/Reveal.vue';
import Alert from '@/Components/UI/Alert.vue';
import Badge from '@/Components/UI/Badge.vue';
import { comparisonRows, faqs, lifecycle, pricingHighlights, site } from '@/content/public.js';

const props = defineProps({
    products: { type: Array, default: () => [] },
    gatewaySettings: { type: Object, default: () => ({}) },
});

const page = usePage();
const isAuthed = computed(() => Boolean(page.props.auth?.user));
const ctaHref = computed(() => (isAuthed.value ? route('store') : route('register')));
const ctaLabel = computed(() => (isAuthed.value ? 'Open the store' : 'Create an account'));

const formatAmount = (amount) =>
    Number(amount).toLocaleString(undefined, {
        minimumFractionDigits: Number(amount) % 1 === 0 ? 0 : 2,
        maximumFractionDigits: 2,
    });

const billingLabel = (period) => (!period ? 'One-time' : period >= 365 ? 'Yearly' : 'Monthly');

const allPrices = computed(() => props.products.flatMap((product) => product.prices ?? []));

const allCurrencies = computed(() => [...new Set(allPrices.value.map((price) => price.currency))]);
const activeCurrency = ref(allCurrencies.value[0] ?? null);
const showCurrencyToggle = computed(() => allCurrencies.value.length > 1);

// Keep every product visible: fall back to its own prices if it has none in the
// selected currency, so switching currency never hides a plan entirely.
const pricesFor = (product) => {
    const list = product.prices ?? [];
    if (!activeCurrency.value) return list;
    const filtered = list.filter((price) => price.currency === activeCurrency.value);
    return filtered.length ? filtered : list;
};

// One card per stored price keeps plan typography large and each cycle explicit.
const planCards = computed(() => {
    const cards = [];
    for (const product of props.products) {
        for (const price of pricesFor(product)) {
            cards.push({
                key: `${product.id}-${price.id}`,
                title: product.name,
                price: `${formatAmount(price.amount)} ${price.currency}`,
                period: billingLabel(price.billing_period),
                description: product.description,
                href: `/pricing/${product.slug}`,
            });
        }
    }
    return cards;
});

const planFeatures = computed(() => pricingHighlights.map((item) => item.title));

const enabledGateways = computed(() => {
    const list = [];
    if (props.gatewaySettings.gateway_stripe_active === '1') list.push('Card (Stripe)');
    if (props.gatewaySettings.gateway_bkash_active === '1') list.push('bKash');
    list.push('Offline bank transfer');
    return list;
});

const columns = [
    { key: 'feature', label: 'Capability' },
    { key: 'basic', label: 'Basic' },
    { key: 'pro', label: 'Pro' },
    { key: 'enterprise', label: 'Enterprise' },
];
</script>

<template>
    <PublicLayout title="Pricing" :description="site.description">
        <!-- Header -->
        <section class="relative overflow-hidden">
            <span class="bg-grid pointer-events-none absolute inset-0 opacity-40" aria-hidden="true" />
            <span class="hero-glow pointer-events-none absolute -top-24 left-1/4 h-72 w-72 rounded-full" aria-hidden="true" />

            <div class="relative mx-auto max-w-6xl px-5 pb-10 pt-16 sm:pt-20">
                <SectionHeading
                    eyebrow="Pricing"
                    title="Simple plans, transparent licensing"
                    description="Every plan uses the same license model. Choose the price that fits, then activate against your domain."
                />

                <div class="mt-7 flex flex-wrap items-center gap-2">
                    <span class="font-mono text-[11.5px] uppercase tracking-widest text-text-muted">Payment methods:</span>
                    <Badge v-for="gateway in enabledGateways" :key="gateway" status="success">{{ gateway }}</Badge>
                </div>
            </div>
        </section>

        <!-- Plans -->
        <section class="mx-auto max-w-6xl px-5 py-14">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <SectionHeading eyebrow="Plans" title="Choose a plan" />

                <div
                    v-if="showCurrencyToggle"
                    class="inline-flex items-center self-start rounded-full border border-panel-line bg-panel-2 p-1"
                    role="group"
                    aria-label="Currency"
                >
                    <button
                        v-for="currency in allCurrencies"
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
            </div>

            <Alert v-if="products.length === 0" class="mt-8">
                No products are available right now. Please check back shortly or
                <Link :href="route('contact')" class="text-brand-primary hover:underline">contact us</Link>.
            </Alert>

            <Reveal v-else class="mt-10 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <PricingCard
                    v-for="card in planCards"
                    :key="card.key"
                    :title="card.title"
                    :price="card.price"
                    :period="card.period"
                    :description="card.description"
                    :features="planFeatures"
                    cta-label="View plans"
                    :cta-href="card.href"
                />
            </Reveal>

            <div v-if="products.length" class="mt-8 flex flex-wrap items-center justify-center gap-x-3 gap-y-2 text-center">
                <span class="text-sm text-text-secondary">Not ready to choose?</span>
                <Link :href="ctaHref" class="text-sm font-medium text-brand-primary hover:underline">{{ ctaLabel }}</Link>
            </div>
        </section>

        <!-- License model -->
        <section class="border-y border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-16">
                <SectionHeading
                    eyebrow="Included in every plan"
                    title="The license model"
                    description="These capabilities apply to all plans and do not require any additional setup."
                />
                <Reveal class="mt-10 grid gap-4 sm:grid-cols-2">
                    <BenefitCard
                        v-for="item in pricingHighlights"
                        :key="item.title"
                        :title="item.title"
                        :body="item.body"
                    >
                        <template #icon>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </template>
                    </BenefitCard>
                </Reveal>
            </div>
        </section>

        <!-- Lifecycle -->
        <section class="mx-auto max-w-6xl px-5 py-20">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                <SectionHeading
                    eyebrow="How a license works"
                    title="What happens after you buy"
                    description="From checkout to expiry — the same steps apply to online and offline-bank-transfer purchases."
                />
                <StepList :steps="lifecycle" />
            </div>
        </section>

        <!-- Comparison -->
        <section class="border-y border-panel-line bg-panel">
            <div class="mx-auto max-w-6xl px-5 py-20">
                <SectionHeading
                    eyebrow="Comparison"
                    title="Plan capabilities side by side"
                    description="Every plan shares the same core protections. Activation limits are set per license when it is issued."
                />
                <div class="mt-10">
                    <ComparisonTable :columns="columns" :rows="comparisonRows" />
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="mx-auto max-w-6xl px-5 py-20">
            <SectionHeading eyebrow="FAQ" title="Questions, answered" align="center" />
            <div class="mx-auto mt-10 max-w-3xl">
                <FAQ :items="faqs" />
            </div>
        </section>

        <!-- CTA -->
        <section class="mx-auto max-w-6xl px-5 pb-20">
            <CTABanner
                title="Ready to issue your first license?"
                body="Create an account, choose a plan, and activate in minutes. Offline bank transfer is available if no online gateway is enabled."
                primary-label="Get started"
                :primary-href="ctaHref"
                secondary-label="Contact sales"
                :secondary-href="route('contact')"
            />
        </section>
    </PublicLayout>
</template>