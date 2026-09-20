<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

// Tiny scroll-reveal wrapper. No library: a single IntersectionObserver adds
// `.is-visible` once. Content is visible without JS and when the observer is
// unsupported; reduced motion is handled in CSS (see `.reveal` in app.css).
const props = defineProps({
    as: { type: String, default: 'div' },
    delay: { type: Number, default: 0 },
});

const el = ref(null);
const visible = ref(false);
let observer = null;

onMounted(() => {
    if (typeof IntersectionObserver === 'undefined') {
        visible.value = true;
        return;
    }

    observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    visible.value = true;
                    observer?.disconnect();
                    observer = null;
                    break;
                }
            }
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.1 },
    );

    if (el.value) observer.observe(el.value);
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>

<template>
    <component
        :is="as"
        ref="el"
        class="reveal"
        :class="{ 'is-visible': visible }"
        :style="delay ? { transitionDelay: `${delay}ms` } : null"
    >
        <slot />
    </component>
</template>
