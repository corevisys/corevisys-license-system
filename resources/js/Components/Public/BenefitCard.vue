<script setup>
// Bento-friendly benefit card. An optional icon slot renders inside a
// brand-tinted tile; the card lifts on hover (see `.card-lift`).
defineProps({
    title: { type: String, required: true },
    body: { type: String, default: '' },
    wide: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="card-lift flex h-full flex-col rounded-2xl border border-panel-line bg-panel p-6"
        :class="wide ? 'sm:col-span-2' : ''"
    >
        <span
            v-if="$slots.icon"
            class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-brand-primary/10 text-brand-primary"
            aria-hidden="true"
        >
            <slot name="icon" />
        </span>
        <h3 class="text-base font-semibold text-text-primary">{{ title }}</h3>
        <p v-if="body" class="mt-2 text-sm leading-relaxed text-text-secondary">{{ body }}</p>
        <slot />
    </div>
</template>
