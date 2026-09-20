<script setup>
defineProps({
    title: { type: String, default: '' },
    body: { type: String, default: '' },
    primaryLabel: { type: String, default: '' },
    primaryHref: { type: String, default: '/pricing' },
    secondaryLabel: { type: String, default: '' },
    secondaryHref: { type: String, default: '/contact' },
});
</script>

<template>
    <div class="rounded-[10px] border border-panel-line bg-panel-2 p-[24px_24px]">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-xl">
                <h2 class="text-lg font-semibold text-text-primary">{{ title }}</h2>
                <p v-if="body" class="mt-2 text-[13.5px] leading-relaxed text-text-secondary">{{ body }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-3">
                <a
                    v-if="primaryLabel"
                    :href="primaryHref"
                    class="inline-flex items-center rounded-lg border border-transparent bg-amber px-[18px] py-[11px] text-[14.5px] font-medium text-[#1A1305] hover:bg-amber-hover"
                >{{ primaryLabel }}</a>
                <a
                    v-if="secondaryLabel"
                    :href="secondaryHref"
                    class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-secondary hover:border-amber-dim"
                >{{ secondaryLabel }}</a>
            </div>
        </div>
    </div>
</template>