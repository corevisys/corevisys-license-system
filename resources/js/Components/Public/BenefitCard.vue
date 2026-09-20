<script setup>
defineProps({
    title: { type: String, required: true },
    body: { type: String, default: '' },
});
</script>

<template>
    <div class="h-full rounded-[10px] border border-panel-line bg-panel-2 p-[20px_22px]">
        <h3 class="text-sm font-semibold text-text-primary">{{ title }}</h3>
        <p v-if="body" class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">{{ body }}</p>
    </div>
</template>