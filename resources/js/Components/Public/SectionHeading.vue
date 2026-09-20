<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
    align: { type: String, default: 'left' },
});
</script>

<template>
    <div :class="align === 'center' ? 'text-center mx-auto max-w-2xl' : 'max-w-2xl'">
        <p v-if="eyebrow" class="mb-3 font-mono text-[11.5px] uppercase tracking-[0.18em] text-amber">{{ eyebrow }}</p>
        <h2 class="text-2xl font-semibold tracking-tight text-text-primary sm:text-3xl">{{ title }}</h2>
        <p v-if="description" class="mt-3 text-sm leading-relaxed text-text-secondary sm:text-[15px]">{{ description }}</p>
    </div>
</template>