<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        default: 'default',
        validator: (value) => ['success', 'danger', 'amber', 'default'].includes(value),
    },
    dot: {
        type: Boolean,
        default: true,
    },
});

const statusClasses = computed(() => ({
    success: 'text-teal border-teal/35',
    danger: 'text-danger border-danger/35',
    amber: 'text-amber border-amber/35',
    default: 'text-text-secondary border-panel-line',
}[props.status]));

const dotClasses = computed(() => ({
    success: 'text-teal',
    danger: 'text-danger',
    amber: 'text-amber',
    default: 'text-text-secondary',
}[props.status]));
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-[5px] font-mono text-[11.5px] px-[9px] py-[3px] rounded-full border',
            statusClasses,
        ]"
    >
        <span v-if="dot" :class="dotClasses" aria-hidden="true">●</span>
        <slot />
    </span>
</template>
