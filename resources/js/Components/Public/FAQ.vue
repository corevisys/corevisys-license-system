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
            class="overflow-hidden rounded-[10px] border border-panel-line bg-panel-2"
        >
            <button
                type="button"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left"
                :aria-expanded="open === index"
                @click="toggle(index)"
            >
                <span class="text-sm font-medium text-text-primary">{{ item.q }}</span>
                <span class="font-mono text-amber" aria-hidden="true">{{ open === index ? '−' : '+' }}</span>
            </button>
            <p
                v-show="open === index"
                class="border-t border-panel-line px-5 py-4 text-[13.5px] leading-relaxed text-text-secondary"
            >{{ item.a }}</p>
        </div>
    </div>
</template>