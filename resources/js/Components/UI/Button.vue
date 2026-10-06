<script setup>
import { computed } from 'vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger'].includes(value),
    },
});

const variantClasses = computed(() => ({
    primary: 'bg-amber text-[#1A1305] border-transparent hover:bg-amber-hover',
    secondary: 'bg-panel-2 text-text-secondary border-panel-line',
    danger: 'bg-danger text-[#2A0F0C] border-transparent',
}[props.variant]));
</script>

<template>
    <button
        v-bind="$attrs"
        :class="[
            'inline-flex items-center justify-center gap-2 rounded-lg px-[18px] py-[11px] text-[14.5px] font-medium cursor-pointer border',
            variantClasses,
        ]"
    >
        <slot name="icon-before" />
        <slot />
        <slot name="icon-after" />
    </button>
</template>
