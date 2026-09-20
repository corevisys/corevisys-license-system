<script setup>
defineProps({
    steps: { type: Array, default: () => [] },
});
</script>

<template>
    <ol class="space-y-3">
        <li
            v-for="(step, index) in steps"
            :key="index"
            class="flex gap-4 rounded-[10px] border border-panel-line bg-panel-2 p-[18px_20px]"
        >
            <span
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-amber/35 font-mono text-[12px] text-amber"
                aria-hidden="true"
            >{{ String(index + 1).padStart(2, '0') }}</span>
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-text-primary">{{ step.title }}</h3>
                <p class="mt-1 text-[13.5px] leading-relaxed text-text-secondary">{{ step.body }}</p>
            </div>
        </li>
    </ol>
</template>