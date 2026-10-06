<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (value) => ['success', 'danger', 'default'].includes(value),
    },
});

const variantClasses = computed(() => ({
    default: 'border-panel-line bg-panel-2 text-text-secondary',
    danger: 'border-danger/35 bg-danger/8 text-danger',
    success: 'border-teal/35 bg-teal/8 text-teal',
}[props.variant]));
</script>

<template>
    <div :class="['flex gap-[10px] rounded-lg px-[14px] py-[12px] text-[13.5px] border', variantClasses]">
        <slot />
    </div>
</template>
