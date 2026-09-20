<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import StepList from '@/Components/public/StepList.vue';
import ComparisonTable from '@/Components/public/ComparisonTable.vue';
import FAQ from '@/Components/public/FAQ.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
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
        <section class="mx-auto max-w-6xl px-5 pb-10 pt-14">
            <SectionHeading
                eyebrow="Pricing"
                title="Simple plans, transparent licensing"
                description="Every plan uses the same license model. Choose the price that fits, then activate against your domain."
            />

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <span class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">Payment methods:</span>
                <Badge v-for="gateway in enabledGateways" :key="gateway" status="success">{{ gateway }}</Badge>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-8">
            <Alert v-if="products.length === 0">
                No products are available right now. Please check back shortly or
                <Link :href="route('contact')" class="text-amber hover:underline">contact us</Link>.
            </Alert>

            <div v-else class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="flex h-full flex-col rounded-[10px] border border-panel-line bg-panel-2 p-[22px_22px]"
                >
                    <h3 class="text-base font-semibold text-text-primary">{{ product.name }}</h3>
                    <p v-if="product.description" class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">
                        {{ product.description }}
                    </p>

                    <ul class="mt-5 space-y-3 border-t border-panel-line pt-5">
                        <li
                            v-for="price in product.prices"
                            :key="price.id"
                            class="flex items-end justify-between gap-3 rounded-lg border border-panel-line bg-panel p-[14px_16px]"
                        >
                            <div>
                                <p class="font-mono text-lg text-text-primary">
                                    {{ formatAmount(price.amount) }}
                                    <span class="text-[12px] text-text-muted">{{ price.currency }}</span>
                                </p>
                                <p class="mt-1 text-[12px] text-text-muted">{{ billingLabel(price.billing_period) }}</p>
                            </div>
                            <span class="font-mono text-[11px] uppercase tracking-wide text-text-muted">{{ price.type }}</span>
                        </li>
                    </ul>

                    <div class="mt-auto space-y-2 pt-6">
                        <Link
                            :href="`/pricing/${product.slug}`"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-transparent bg-amber px-[18px] py-[11px] text-[14.5px] font-medium text-[#1A1305] hover:bg-amber-hover"
                        >View plans</Link>
                        <Link
                            :href="ctaHref"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-secondary hover:border-amber-dim"
                        >{{ ctaLabel }}</Link>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                eyebrow="Included in every plan"
                title="The license model"
                description="These capabilities apply to all plans and do not require any additional setup."
            />
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div
                    v-for="item in pricingHighlights"
                    :key="item.title"
                    class="rounded-[10px] border border-panel-line bg-panel-2 p-[20px_22px]"
                >
                    <h3 class="text-sm font-semibold text-text-primary">{{ item.title }}</h3>
                    <p class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">{{ item.body }}</p>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                <SectionHeading
                    eyebrow="How a license works"
                    title="What happens after you buy"
                    description="From checkout to expiry — the same steps apply to online and offline-bank-transfer purchases."
                />
                <StepList :steps="lifecycle" />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                eyebrow="Comparison"
                title="Plan capabilities side by side"
                description="Every plan shares the same core protections. Activation limits are set per license when it is issued."
            />
            <div class="mt-8">
                <ComparisonTable :columns="columns" :rows="comparisonRows" />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading eyebrow="FAQ" title="Questions, answered" align="center" />
            <div class="mx-auto mt-8 max-w-3xl">
                <FAQ :items="faqs" />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
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