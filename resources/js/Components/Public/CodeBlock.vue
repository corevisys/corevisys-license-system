<script setup>
import { ref } from 'vue';

defineProps({
    heading: { type: String, default: '' },
    caption: { type: String, default: '' },
});

const copied = ref(false);

const copy = async (event) => {
    const target = event.currentTarget.closest('[data-code]')?.querySelector('pre')?.innerText ?? '';
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
    <div data-code class="overflow-hidden rounded-2xl border border-panel-line bg-panel">
        <!-- Terminal window bar: neutral dots only (no red/yellow/green). -->
        <div class="flex items-center justify-between gap-3 border-b border-panel-line bg-panel-2 px-4 py-2.5">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex shrink-0 items-center gap-1.5" aria-hidden="true">
                    <span class="h-2.5 w-2.5 rounded-full border border-panel-line bg-panel" />
                    <span class="h-2.5 w-2.5 rounded-full border border-panel-line bg-panel" />
                    <span class="h-2.5 w-2.5 rounded-full border border-panel-line bg-panel" />
                </span>
                <span class="truncate font-mono text-[11.5px] uppercase tracking-wide text-text-muted">{{ heading }}</span>
            </div>
            <button
                type="button"
                class="shrink-0 rounded-md border border-panel-line px-2 py-1 font-mono text-[11px] text-text-secondary transition-colors hover:border-brand-primary/40 hover:text-brand-primary"
                @click="copy"
            >{{ copied ? 'copied' : 'copy' }}</button>
        </div>
        <pre class="overflow-x-auto px-4 py-4 font-mono text-[12.5px] leading-[1.8] text-text-secondary"><code><slot /></code></pre>
        <p v-if="caption" class="border-t border-panel-line px-4 py-2 text-[12px] text-text-muted">{{ caption }}</p>
    </div>
</template>
