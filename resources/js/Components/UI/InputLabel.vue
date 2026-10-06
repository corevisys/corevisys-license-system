<script setup>
defineOptions({ inheritAttrs: false });
</script>

<template>
    <label
        v-bind="$attrs"
        class="block font-mono text-[11.5px] tracking-wide text-text-secondary mb-[7px]"
    >
        <slot />
    </label>
</template>
