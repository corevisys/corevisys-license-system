<script setup>
import { ref } from 'vue';

defineProps({
    items: { type: Array, default: () => [] },
});

const open = ref(0);

const toggle = (index) => {
    open.value = open.value === index ? -1 : index;
};
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="(item, index) in items"
            :key="index"
            class="overflow-hidden rounded-2xl border border-panel-line bg-panel transition-colors"
            :class="open === index ? 'border-brand-primary/40' : ''"
        >
            <button
                type="button"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                :aria-expanded="open === index"
                @click="toggle(index)"
            >
                <span class="text-sm font-medium text-text-primary">{{ item.q }}</span>
                <svg
                    class="h-4 w-4 shrink-0 text-brand-primary transition-transform duration-300 motion-reduce:transition-none"
                    :class="open === index ? 'rotate-180' : ''"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6" /></svg>
            </button>
            <div
                class="grid transition-all duration-300 motion-reduce:transition-none"
                :class="open === index ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'"
            >
                <div class="overflow-hidden">
                    <p class="border-t border-panel-line px-5 py-4 text-sm leading-relaxed text-text-secondary">{{ item.a }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
