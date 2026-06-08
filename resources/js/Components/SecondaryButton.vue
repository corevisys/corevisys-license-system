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
        class="px-8 py-3 bg-white/5 text-text-muted rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-white/10 transition-all border border-white/5 shadow-sm disabled:opacity-50"
    >
        <slot />
    </button>
</template>
