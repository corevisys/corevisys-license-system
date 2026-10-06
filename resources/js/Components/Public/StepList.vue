<script setup>
// Vertical numbered step list. Used where a compact list reads better than the
// horizontal Timeline (product, developers, pricing).
defineProps({
    steps: { type: Array, default: () => [] },
});
</script>

<template>
    <ol class="space-y-3">
        <li
            v-for="(step, index) in steps"
            :key="index"
            class="card-lift flex gap-4 rounded-2xl border border-panel-line bg-panel p-5"
        >
            <span
                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-brand-primary/40 bg-brand-primary/10 font-mono text-[12px] text-brand-primary"
                aria-hidden="true"
            >{{ String(index + 1).padStart(2, '0') }}</span>
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-text-primary">{{ step.title }}</h3>
                <p class="mt-1.5 text-sm leading-relaxed text-text-secondary">{{ step.body }}</p>
            </div>
        </li>
    </ol>
</template>
