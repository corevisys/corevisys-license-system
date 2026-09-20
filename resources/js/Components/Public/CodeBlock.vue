<script setup>
import { ref } from 'vue';

defineProps({
    heading: { type: String, default: '' },
    caption: { type: String, default: '' },
});

const copied = ref(false);

const copy = async (event) => {
    const target = event.currentTarget.closest('[data-code]')?.innerText ?? '';
    try {
        await navigator.clipboard.writeText(target);
        copied.value = true;
        setTimeout(() => (copied.value = false), 1500);
    } catch (error) {
        copied.value = false;
    }
};
</script>

<template>
    <div data-code class="overflow-hidden rounded-[10px] border border-panel-line bg-panel-2">
        <div class="flex items-center justify-between border-b border-panel-line px-4 py-2">
            <span class="font-mono text-[11.5px] uppercase tracking-wide text-text-muted">{{ heading }}</span>
            <button
                type="button"
                class="font-mono text-[11.5px] text-amber hover:underline"
                @click="copy"
            >{{ copied ? 'copied' : 'copy' }}</button>
        </div>
        <pre class="overflow-x-auto px-4 py-4 font-mono text-[12.5px] leading-[1.8] text-text-secondary"><code><slot /></code></pre>
        <p v-if="caption" class="border-t border-panel-line px-4 py-2 text-[12px] text-text-muted">{{ caption }}</p>
    </div>
</template>