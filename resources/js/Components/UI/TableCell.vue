<script setup>
defineProps({
    mono: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <td :class="[ 'px-4 py-3 text-text-primary', mono && 'font-mono text-text-secondary' ]">
        <slot />
    </td>
</template>
