<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';

// Sticky table of contents with active-section highlighting via
// IntersectionObserver. Falls back to plain anchor links without JS.
const props = defineProps({
    items: { type: Array, default: () => [] }, // [{ id, label }]
    label: { type: String, default: 'On this page' },
});

const active = ref(props.items[0]?.id ?? '');
let observer = null;

onMounted(() => {
    if (typeof IntersectionObserver === 'undefined') return;

    const targets = props.items
        .map((item) => document.getElementById(item.id))
        .filter(Boolean);

    if (!targets.length) return;

    observer = new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
            if (visible[0]) active.value = visible[0].target.id;
        },
        { rootMargin: '-96px 0px -70% 0px', threshold: 0 },
    );

    targets.forEach((target) => observer.observe(target));
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
});
</script>

<template>
    <nav aria-label="On this page" class="lg:sticky lg:top-24">
        <p class="mb-3 font-mono text-[11px] uppercase tracking-widest text-text-muted">{{ label }}</p>
        <ul class="space-y-1 border-l border-panel-line">
            <li v-for="item in items" :key="item.id">
                <a
                    :href="`#${item.id}`"
                    class="-ml-px block border-l-2 py-1.5 pl-4 text-[13px] transition-colors"
                    :class="active === item.id
                        ? 'border-brand-primary text-brand-primary'
                        : 'border-transparent text-text-secondary hover:border-panel-line hover:text-text-primary'"
                >{{ item.label }}</a>
            </li>
        </ul>
    </nav>
</template>
