<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
    align: { type: String, default: 'left' },
});
</script>

<template>
    <div :class="align === 'center' ? 'mx-auto max-w-2xl text-center' : 'max-w-2xl'">
        <p
            v-if="eyebrow"
            class="mb-3 font-mono text-xs uppercase tracking-widest text-brand-primary"
        >{{ eyebrow }}</p>
        <h2 class="text-3xl font-semibold leading-tight tracking-tight text-text-primary sm:text-4xl">{{ title }}</h2>
        <p v-if="description" class="mt-4 text-base leading-relaxed text-text-secondary">{{ description }}</p>
    </div>
</template>
