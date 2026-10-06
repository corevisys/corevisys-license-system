<script setup>
defineOptions({ inheritAttrs: false });

const model = defineModel({
    type: String,
    default: '',
});
</script>

<template>
    <input
        v-bind="$attrs"
        v-model="model"
        class="w-full bg-panel-2 border border-panel-line rounded-lg px-[13px] py-[11px] font-mono text-sm text-text-primary outline-none placeholder:text-text-muted focus:border-amber-dim focus:ring-[3px] focus:ring-amber/10"
    >
</template>
