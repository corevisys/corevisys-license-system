<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    title: { type: String, required: true },
    price: { type: String, default: '' },
    period: { type: String, default: '' },
    description: { type: String, default: '' },
    features: { type: Array, default: () => [] },
    ctaLabel: { type: String, default: 'Get started' },
    ctaHref: { type: String, default: '' },
    featured: { type: Boolean, default: false },
});

const ctaClasses = computed(() => [
    'inline-flex w-full items-center justify-center gap-2 rounded-lg border px-[18px] py-[11px] text-[14.5px] font-medium transition-transform hover:scale-[1.02] motion-reduce:hover:scale-100',
    props.featured
        ? 'bg-brand-primary text-bg-dark border-transparent'
        : 'bg-panel-2 text-text-secondary border-panel-line hover:border-brand-primary/40',
]);
</script>

<template>
    <div
        class="card-lift flex h-full flex-col rounded-3xl border p-6"
        :class="featured ? 'border-brand-primary/45 bg-panel' : 'border-panel-line bg-panel'"
    >
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-base font-semibold text-text-primary">{{ title }}</h3>
            <span v-if="featured" class="rounded-full border border-brand-primary/35 px-2.5 py-0.5 font-mono text-[10.5px] uppercase tracking-wide text-brand-primary">Popular</span>
        </div>

        <p v-if="price" class="mt-5 flex items-baseline gap-1.5">
            <span class="font-mono text-4xl font-semibold tracking-tight text-text-primary">{{ price }}</span>
            <span v-if="period" class="text-[12px] text-text-muted">{{ period }}</span>
        </p>

        <p v-if="description" class="mt-3 text-sm leading-relaxed text-text-secondary">{{ description }}</p>

        <ul v-if="features.length" class="mt-6 space-y-2.5 border-t border-panel-line pt-6">
            <li v-for="(feature, index) in features" :key="index" class="flex gap-2.5 text-sm text-text-secondary">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                <span>{{ feature }}</span>
            </li>
        </ul>

        <div class="mt-auto pt-6">
            <Link :href="ctaHref || '/'" :class="ctaClasses">{{ ctaLabel }}</Link>
        </div>
    </div>
</template>
