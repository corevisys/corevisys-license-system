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
    <div class="relative overflow-hidden rounded-3xl border border-panel-line bg-panel p-8 sm:p-12">
        <span class="bg-grid pointer-events-none absolute inset-0 opacity-60" aria-hidden="true" />
        <span class="hero-glow animate-pulse-glow pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full" aria-hidden="true" />

        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-xl">
                <h2 class="text-2xl font-semibold tracking-tight text-text-primary sm:text-3xl">{{ title }}</h2>
                <p v-if="body" class="mt-3 text-sm leading-relaxed text-text-secondary sm:text-base">{{ body }}</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-3">
                <a
                    v-if="primaryLabel"
                    :href="primaryHref"
                    class="inline-flex items-center rounded-lg border border-transparent bg-brand-primary px-[18px] py-[11px] text-[14.5px] font-medium text-bg-dark transition-transform hover:scale-[1.03] motion-reduce:hover:scale-100"
                >{{ primaryLabel }}</a>
                <a
                    v-if="secondaryLabel"
                    :href="secondaryHref"
                    class="inline-flex items-center rounded-lg border border-panel-line bg-panel-2 px-[18px] py-[11px] text-[14.5px] font-medium text-text-secondary transition-colors hover:border-brand-primary/40"
                >{{ secondaryLabel }}</a>
            </div>
        </div>
    </div>
</template>
