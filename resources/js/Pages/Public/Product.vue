<script setup>
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import StepList from '@/Components/public/StepList.vue';
import FAQ from '@/Components/public/FAQ.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
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

        <section class="mx-auto max-w-6xl px-5 pb-10 pt-14">
            <nav aria-label="Breadcrumb" class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">
                <ol class="flex flex-wrap items-center gap-2">
                    <li>
                        <Link :href="route('pricing')" class="hover:text-amber">{{ productPage.breadcrumbPricing }}</Link>
                    </li>
                    <li aria-hidden="true">/</li>
                    <li class="text-text-secondary">{{ product.name }}</li>
                </ol>
            </nav>

            <div class="mt-6 grid gap-10 lg:grid-cols-[1.15fr_1fr]">
                <div>
                    <Badge status="amber">Product</Badge>
                    <h1 class="mt-4 text-3xl font-semibold tracking-tight text-text-primary sm:text-4xl">
                        {{ product.name }}
                    </h1>
                    <p v-if="product.description" class="mt-4 max-w-2xl text-sm leading-relaxed text-text-secondary sm:text-[15px]">
                        {{ product.description }}
                    </p>

                    <div class="mt-6 flex flex-wrap items-end gap-x-6 gap-y-3">
                        <div>
                            <p class="font-mono text-[11px] uppercase tracking-wide text-text-muted">{{ productPage.startingFrom }}</p>
                            <p class="mt-1 font-mono text-3xl text-text-primary">{{ startingLabel || '\u2014' }}</p>
                        </div>
                        <div v-if="showCurrencyToggle" class="flex items-center gap-2" role="group" aria-label="Currency">
                            <button
                                v-for="currency in currencies"
                                :key="currency"
                                type="button"
                                class="rounded-lg border px-3 py-1.5 font-mono text-[12px]"
                                :class="currency === activeCurrency
                                    ? 'border-amber bg-amber/10 text-amber'
                                    : 'border-panel-line bg-panel-2 text-text-muted hover:border-amber-dim'"
                                :aria-pressed="currency === activeCurrency"
                                @click="activeCurrency = currency"
                            >{{ currency }}</button>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <Link
                            :href="storeHref"
                            class="inline-flex items-center rounded-lg border border-transparent bg-amber px-[18px] py-[11px] text-[14.5px] font-medium text-[#1A1305] hover:bg-amber-hover"
                        >{{ productPage.buyLabel }}</Link>
                        <Link
                            v-if="supportsTrial"
                            :href="storeHref"
                            class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-secondary hover:border-amber-dim"
                        >{{ productPage.trialLabel }}</Link>
                        <Link
                            v-else
                            :href="route('contact')"
                            class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-secondary hover:border-amber-dim"
                        >{{ productPage.askTrialLabel }}</Link>
                    </div>

                    <p class="mt-3 max-w-xl text-[12.5px] leading-relaxed text-text-muted">{{ productPage.checkoutNote }}</p>
                    <p v-if="supportsTrial" class="mt-1 max-w-xl text-[12.5px] leading-relaxed text-text-muted">
                        {{ productPage.trialNote }}
                    </p>
                </div>

                <dl class="grid gap-px self-start overflow-hidden rounded-[10px] border border-panel-line bg-panel-line sm:grid-cols-2">
                    <div v-for="fact in activationFacts.slice(0, 6)" :key="fact.label" class="bg-panel-2 p-[16px_18px]">
                        <dt class="font-mono text-[11px] uppercase tracking-wide text-text-muted">{{ fact.label }}</dt>
                        <dd class="mt-2 text-[13.5px] font-medium text-text-primary">{{ fact.value }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                eyebrow="Product"
                :title="productPage.featuresHeading"
                description="What is included is taken from the stored product data — nothing is invented."
            />
            <div class="mt-8 rounded-[10px] border border-dashed border-panel-line bg-panel-2 p-[22px]">
                <p class="font-mono text-[13px] text-text-muted">{{ productFeaturesPlaceholder }}</p>
                <p class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">{{ productPage.featuresEmptyNote }}</p>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                eyebrow="Plans"
                :title="productPage.plansHeading"
                description="Billing cycles are exactly as stored on each price. Toggle the currency to see other available prices."
            />

            <p v-if="visiblePrices.length === 0" class="mt-8 text-[13.5px] text-text-secondary">
                {{ productPage.plansEmptyNote }}
            </p>

            <div v-else class="mt-8 overflow-x-auto rounded-[10px] border border-panel-line">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr class="bg-panel">
                            <th scope="col" class="px-5 py-3 font-mono text-[11px] uppercase tracking-wide text-text-muted">
                                {{ productPage.planColumn }}
                            </th>
                            <th scope="col" class="px-5 py-3 font-mono text-[11px] uppercase tracking-wide text-text-muted">
                                {{ productPage.cycleColumn }}
                            </th>
                            <th scope="col" class="px-5 py-3 text-right font-mono text-[11px] uppercase tracking-wide text-text-muted">
                                {{ productPage.amountColumn }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="price in visiblePrices" :key="price.id" class="border-t border-panel-line bg-panel-2">
                            <td class="px-5 py-4 text-[13.5px] capitalize text-text-primary">{{ price.type }}</td>
                            <td class="px-5 py-4 text-[13.5px] text-text-secondary">{{ billingLabel(price.billing_period) }}</td>
                            <td class="px-5 py-4 text-right font-mono text-[14px] text-text-primary">
                                {{ formatAmount(price.amount) }}
                                <span class="text-[12px] text-text-muted">{{ price.currency }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                <SectionHeading
                    eyebrow="License model"
                    :title="productPage.licenceHeading"
                    description="The same lifecycle runs for every product, whether the customer pays online or by bank transfer."
                />
                <StepList :steps="lifecycle" />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                eyebrow="Details"
                :title="productPage.notesHeading"
                description="The practical rules that apply to this license once it is issued."
            />
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="note in licenseNotes" :key="note.title" class="rounded-[10px] border border-panel-line bg-panel-2 p-[20px_22px]">
                    <h3 class="text-sm font-semibold text-text-primary">{{ note.title }}</h3>
                    <p class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">{{ note.body }}</p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                <div>
                    <SectionHeading
                        eyebrow="Payment"
                        :title="productPage.paymentHeading"
                        description="Which gateways are live depends on this deployment's settings."
                    />
                    <div class="mt-6 flex flex-wrap items-center gap-2">
                        <span class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Payment methods:</span>
                        <Badge v-for="gateway in enabledGateways" :key="gateway" status="success">{{ gateway }}</Badge>
                    </div>
                    <Alert class="mt-5">{{ productPaymentNote }}</Alert>
                </div>
                <StepList :steps="paymentFlow" />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading eyebrow="FAQ" :title="productPage.faqHeading" align="center" />
            <div class="mx-auto mt-8 max-w-3xl">
                <FAQ :items="productFaqs" />
            </div>
        </section>

        <section v-if="otherList.length" class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading eyebrow="Catalogue" :title="productPage.otherHeading" :description="productPage.otherNote" />
            <div class="mt-8 grid gap-5 md:grid-cols-2">
                <div
                    v-for="item in otherList"
                    :key="item.id"
                    class="flex h-full flex-col rounded-[10px] border border-panel-line bg-panel-2 p-[22px]"
                >
                    <h3 class="text-sm font-semibold text-text-primary">{{ item.name }}</h3>
                    <p v-if="item.description" class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">
                        {{ item.description }}
                    </p>
                    <div class="mt-auto pt-5">
                        <Link
                            :href="`/pricing/${item.slug}`"
                            class="inline-flex items-center rounded-lg border border-panel-line bg-panel px-[16px] py-[9px] text-[13.5px] font-medium text-text-secondary hover:border-amber-dim"
                        >View {{ item.name }}</Link>
                    </div>
                </div>
            </div>
            <div class="mt-6">
                <Link :href="route('pricing')" class="text-[13.5px] text-amber hover:underline">{{ productPage.otherLink }}</Link>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
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