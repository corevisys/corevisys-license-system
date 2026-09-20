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
    'inline-flex w-full items-center justify-center gap-2 rounded-lg border px-[18px] py-[11px] text-[14.5px] font-medium cursor-pointer',
    props.featured
        ? 'bg-amber text-[#1A1305] border-transparent hover:bg-amber-hover'
        : 'bg-panel-2 text-text-secondary border-panel-line hover:border-amber-dim',
]);
</script>

<template>
    <div
        class="flex h-full flex-col rounded-[10px] border p-[22px_22px]"
        :class="featured ? 'border-amber/45 bg-panel-2' : 'border-panel-line bg-panel-2'"
    >
        <div class="flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-text-primary">{{ title }}</h3>
            <span v-if="featured" class="font-mono text-[11px] uppercase tracking-wide text-amber">Popular</span>
        </div>

        <p v-if="price" class="mt-4 font-mono text-2xl text-text-primary">
            {{ price }}
            <span v-if="period" class="text-[12px] text-text-muted">{{ period }}</span>
        </p>

        <p v-if="description" class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">{{ description }}</p>

        <ul v-if="features.length" class="mt-5 space-y-2 border-t border-panel-line pt-5">
            <li v-for="(feature, index) in features" :key="index" class="flex gap-2 text-[13.5px] text-text-secondary">
                <span class="text-amber" aria-hidden="true">+</span>
                <span>{{ feature }}</span>
            </li>
        </ul>

        <div class="mt-6 pt-1 mt-auto">
            <Link :href="ctaHref || '/'" :class="ctaClasses">{{ ctaLabel }}</Link>
        </div>
    </div>
</template>