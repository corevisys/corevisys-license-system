<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="px-8 py-3 bg-panel-2 text-text-secondary rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-panel transition-all border border-panel-line shadow-sm disabled:opacity-50"
    >
        <slot />
    </button>
</template>
