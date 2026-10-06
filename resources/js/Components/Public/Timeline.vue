<script setup>
// Horizontal numbered timeline on desktop, vertical on mobile. The connector
// line is built from border-panel-line; the numbered nodes use brand tokens.
defineProps({
    steps: { type: Array, default: () => [] },
});
</script>

<template>
    <ol class="relative grid gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-6">
        <li
            v-for="(step, index) in steps"
            :key="index"
            class="relative flex gap-4 lg:block"
        >
            <!-- Connector line (desktop only) -->
            <span
                v-if="index < steps.length - 1"
                class="absolute left-[15px] top-8 hidden h-px w-full bg-panel-line lg:block"
                aria-hidden="true"
            />

            <span
                class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-brand-primary/40 bg-panel font-mono text-[12px] text-brand-primary"
                aria-hidden="true"
            >{{ String(index + 1).padStart(2, '0') }}</span>

            <div class="min-w-0 lg:mt-4">
                <h3 class="text-sm font-semibold text-text-primary">{{ step.title }}</h3>
                <p class="mt-1.5 text-[13.5px] leading-relaxed text-text-secondary">{{ step.body }}</p>
            </div>
        </li>
    </ol>
</template>
