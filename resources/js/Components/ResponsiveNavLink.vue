<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-amber text-start text-base font-medium text-amber bg-panel-2 focus:outline-none focus:text-amber focus:bg-panel-2 focus:border-amber transition duration-150 ease-in-out'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-text-secondary hover:text-text-primary hover:bg-panel-2 hover:border-panel-line focus:outline-none focus:text-text-primary focus:bg-panel-2 focus:border-panel-line transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
