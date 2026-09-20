<script setup>
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Components/public/PublicLayout.vue';
import SectionHeading from '@/Components/public/SectionHeading.vue';
import StepList from '@/Components/public/StepList.vue';
import BenefitCard from '@/Components/public/BenefitCard.vue';
import CTABanner from '@/Components/public/CTABanner.vue';
import Badge from '@/Components/UI/Badge.vue';
import {
    activationFacts,
    lifecycle,
    pricingHighlights,
    site,
} from '@/content/public.js';

defineProps({
    featuredProducts: { type: Array, default: () => [] },
});
</script>

<template>
    <PublicLayout :title="site.name" :description="site.description">
        <section class="mx-auto max-w-6xl px-5 pb-14 pt-16">
            <Badge status="amber">Software licensing platform</Badge>
            <h1 class="mt-5 max-w-3xl text-3xl font-semibold leading-tight tracking-tight text-text-primary sm:text-5xl">
                {{ site.tagline }}
            </h1>
            <p class="mt-5 max-w-2xl text-sm leading-relaxed text-text-secondary sm:text-base">
                {{ site.description }}
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <Link
                    :href="route('pricing')"
                    class="inline-flex items-center rounded-lg border border-transparent bg-amber px-[18px] py-[11px] text-[14.5px] font-medium text-[#1A1305] hover:bg-amber-hover"
                >See pricing</Link>
                <Link
                    :href="route('developers')"
                    class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-secondary hover:border-amber-dim"
                >Read the API docs</Link>
            </div>

            <dl class="mt-12 grid gap-px overflow-hidden rounded-[10px] border border-panel-line bg-panel-line sm:grid-cols-2 lg:grid-cols-4">
                <div v-for="fact in activationFacts.slice(0, 4)" :key="fact.label" class="bg-panel-2 p-[18px_20px]">
                    <dt class="font-mono text-[11px] uppercase tracking-wide text-text-muted">{{ fact.label }}</dt>
                    <dd class="mt-2 text-sm font-medium text-text-primary">{{ fact.value }}</dd>
                </div>
            </dl>
        </section>

        <section v-if="featuredProducts.length" class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                eyebrow="Products"
                title="Pick a product to see its plans"
                description="Each product page shows its own prices, billing cycles, and the license notes that apply to it."
            />
            <div class="mt-8 grid gap-5 md:grid-cols-3">
                <Link
                    v-for="product in featuredProducts"
                    :key="product.id"
                    :href="`/pricing/${product.slug}`"
                    class="flex h-full flex-col rounded-[10px] border border-panel-line bg-panel-2 p-[22px] hover:border-amber-dim"
                >
                    <h3 class="text-sm font-semibold text-text-primary">{{ product.name }}</h3>
                    <p v-if="product.description" class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">
                        {{ product.description }}
                    </p>
                    <span class="mt-auto pt-5 font-mono text-[12px] text-amber">View plans &rarr;</span>
                </Link>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr]">
                <div>
                    <SectionHeading
                        eyebrow="How it works"
                        title="From checkout to offline verification"
                        description="The same lifecycle runs for every license, whether the customer pays online or by bank transfer."
                    />
                    <div class="mt-8">
                        <BenefitCard
                            title="Built for real deployments"
                            body="Licenses are verified by the software that uses them, not just by the dashboard. Signatures let your app trust a response even when it cannot reach the network."
                        />
                    </div>
                </div>
                <StepList :steps="lifecycle" />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <SectionHeading
                align="center"
                eyebrow="Why CoreVisys"
                title="Licensing controls that protect your product"
                description="Everything below is part of the default license model — no extra integration required."
            />
            <div class="mt-9 grid gap-4 sm:grid-cols-2">
                <BenefitCard
                    v-for="item in pricingHighlights"
                    :key="item.title"
                    :title="item.title"
                    :body="item.body"
                />
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-5 py-14">
            <CTABanner
                title="Create an account and issue your first license"
                body="Start with the pricing page, pick a plan, and activate a license in minutes."
                primary-label="Get started"
                :primary-href="route('pricing')"
                secondary-label="Talk to us"
                :secondary-href="route('contact')"
            />
        </section>
    </PublicLayout>
</template>